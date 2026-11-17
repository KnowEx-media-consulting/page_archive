<?php

declare(strict_types=1);

namespace Knowex\PageArchive\Service;

use Doctrine\DBAL\ArrayParameterType;
use Doctrine\DBAL\Exception;
use Knowex\PageArchive\Exception\ArchiveException;
use TYPO3\CMS\Core\Authentication\BackendUserAuthentication;
use TYPO3\CMS\Core\Database\Connection;
use TYPO3\CMS\Core\Database\ConnectionPool;
use TYPO3\CMS\Core\Database\Query\QueryBuilder;
use TYPO3\CMS\Core\Database\Query\Restriction\DeletedRestriction;
use TYPO3\CMS\Core\Database\Query\Restriction\WorkspaceRestriction;
use TYPO3\CMS\Core\DataHandling\DataHandler;
use TYPO3\CMS\Core\Localization\LanguageService;
use TYPO3\CMS\Core\Localization\LanguageServiceFactory;
use TYPO3\CMS\Core\Type\Bitmask\Permission;
use TYPO3\CMS\Core\Utility\GeneralUtility;

/**
 * Archiviert Seitenzweige und stellt sie wieder her.
 *
 * Archivieren heißt: Seitentyp "Archiv" und deaktiviert; der vorherige Zustand wird in der Seite gemerkt.
 */
final class ArchiveService
{
    public const int DOKTYPE_ARCHIVE = 187;

    private const string TABLE = 'pages';
    private const string LANGUAGE_FILE = 'LLL:EXT:page_archive/Resources/Private/Language/locallang.xlf:';
    private const int CHUNK_SIZE = 500;

    public function __construct(
        private readonly ConnectionPool $connectionPool,
        private readonly LanguageServiceFactory $languageServiceFactory,
    ) {}

    /**
     * Archiviert die Seite samt allen Unterseiten.
     *
     * @return int Anzahl der archivierten Seiten
     * @throws Exception
     */
    public function archive(int $pageUid): int
    {
        $pages = $this->getBranch($pageUid);
        foreach ($pages as $page) {
            if ((int)$page['is_siteroot'] === 1) {
                throw new ArchiveException($this->translate('error.siteRoot'), 1763000001);
            }
        }
        $pages = array_filter($pages, static fn(array $page): bool => !self::isArchived($page));
        if ($pages === []) {
            throw new ArchiveException($this->translate('error.alreadyArchived'), 1763000002);
        }
        $this->assertAccess($pages, [self::DOKTYPE_ARCHIVE]);

        $data = [];
        foreach ($pages as $page) {
            $data[self::TABLE][(int)$page['uid']] = [
                'doktype' => self::DOKTYPE_ARCHIVE,
                'hidden' => 1,
                'tx_pagearchive_original_doktype' => (int)$page['doktype'],
                'tx_pagearchive_original_hidden' => (int)$page['hidden'],
                'tx_pagearchive_archived_at' => (int)$GLOBALS['EXEC_TIME'],
            ];
        }
        $this->process($data);

        return count($pages);
    }

    /**
     * Stellt die archivierte Seite samt allen archivierten Unterseiten im ursprünglichen Zustand wieder her.
     *
     * @return int Anzahl der wiederhergestellten Seiten
     * @throws Exception
     */
    public function restore(int $pageUid): int
    {
        $pages = $this->getBranch($pageUid);
        if (!self::isArchived($pages[0])) {
            throw new ArchiveException($this->translate('error.notArchived'), 1763000003);
        }
        $parent = $this->getPage((int)$pages[0]['pid']);
        if ($parent !== null && self::isArchived($parent)) {
            // Sonst wäre die Seite wiederhergestellt, bliebe aber unter der archivierten Elternseite unsichtbar.
            throw new ArchiveException($this->translate('error.parentArchived'), 1763000004);
        }
        $pages = array_filter($pages, self::isArchived(...));
        $this->assertAccess($pages, array_map(self::getOriginalDoktype(...), $pages));

        $data = [];
        foreach ($pages as $page) {
            $data[self::TABLE][(int)$page['uid']] = [
                'doktype' => self::getOriginalDoktype($page),
                'hidden' => (int)$page['tx_pagearchive_original_hidden'],
                'tx_pagearchive_original_doktype' => 0,
                'tx_pagearchive_original_hidden' => 0,
                'tx_pagearchive_archived_at' => 0,
            ];
        }
        $this->process($data);

        return count($pages);
    }

    /**
     * @param array<string, mixed> $page
     */
    private static function getOriginalDoktype(array $page): int
    {
        // Ohne gemerkten Typ (z. B. direkt in der Datenbank umgestellt) wird es eine Standardseite.
        return (int)($page['tx_pagearchive_original_doktype'] ?? 0) ?: 1;
    }

    /**
     * @param array<string, mixed> $page
     */
    private static function isArchived(array $page): bool
    {
        return (int)$page['doktype'] === self::DOKTYPE_ARCHIVE;
    }

    /**
     * Die Seite selbst (erster Eintrag) und alle Unterseiten der Standardsprache, auch deaktivierte.
     *
     * @return list<array<string, mixed>>
     * @throws Exception
     */
    private function getBranch(int $pageUid): array
    {
        $root = $this->getPage($pageUid);
        if ($root === null || (int)$root['sys_language_uid'] !== 0) {
            throw new ArchiveException($this->translate('error.notFound', [$pageUid]), 1763000005);
        }

        $pages = [$root];
        $parentUids = [$pageUid];
        while ($parentUids !== []) {
            $children = [];
            foreach (array_chunk($parentUids, self::CHUNK_SIZE) as $chunk) {
                $queryBuilder = $this->createQueryBuilder();
                $children = array_merge($children, $queryBuilder
                    ->select('*')
                    ->from(self::TABLE)
                    ->where(
                        $queryBuilder->expr()->in(
                            'pid',
                            $queryBuilder->createNamedParameter($chunk, ArrayParameterType::INTEGER)
                        ),
                        $queryBuilder->expr()->eq('sys_language_uid', 0)
                    )
                    ->executeQuery()
                    ->fetchAllAssociative());
            }
            $pages = array_merge($pages, $children);
            $parentUids = array_map(static fn(array $page): int => (int)$page['uid'], $children);
        }
        return $pages;
    }

    /**
     * @return array<string, mixed>|null
     * @throws Exception
     */
    private function getPage(int $pageUid): ?array
    {
        if ($pageUid <= 0) {
            return null;
        }
        $queryBuilder = $this->createQueryBuilder();
        $page = $queryBuilder
            ->select('*')
            ->from(self::TABLE)
            ->where($queryBuilder->expr()->eq('uid', $queryBuilder->createNamedParameter($pageUid, Connection::PARAM_INT)))
            ->executeQuery()
            ->fetchAssociative();
        return $page ?: null;
    }

    /**
     * Prüft vorab alle Rechte, damit der DataHandler einen Zweig nicht nur zur Hälfte umstellt.
     *
     * @param array<array<string, mixed>> $pages
     * @param array<int> $doktypes Seitentypen, die gesetzt werden sollen
     */
    private function assertAccess(array $pages, array $doktypes): void
    {
        $backendUser = $this->getBackendUser();
        if ($backendUser->isAdmin()) {
            return;
        }
        foreach (array_unique($doktypes) as $doktype) {
            if (!GeneralUtility::inList($backendUser->groupData['pagetypes_select'] ?? '', (string)$doktype)) {
                throw new ArchiveException($this->translate('error.pageType', [$doktype]), 1763000006);
            }
        }
        if (
            !$backendUser->check('non_exclude_fields', self::TABLE . ':doktype')
            || !$backendUser->check('non_exclude_fields', self::TABLE . ':hidden')
        ) {
            throw new ArchiveException($this->translate('error.fields'), 1763000007);
        }
        foreach ($pages as $page) {
            if (
                !$backendUser->doesUserHaveAccess($page, Permission::PAGE_EDIT)
                || $backendUser->isInWebMount($page) === null
            ) {
                throw new ArchiveException(
                    $this->translate('error.access', [(string)$page['title'], (int)$page['uid']]),
                    1763000008
                );
            }
        }
    }

    /**
     * @param array<string, array<int, array<string, int>>> $data
     */
    private function process(array $data): void
    {
        $dataHandler = GeneralUtility::makeInstance(DataHandler::class);
        $dataHandler->start($data, []);
        $dataHandler->process_datamap();
        if ($dataHandler->errorLog !== []) {
            throw new ArchiveException(implode(' ', $dataHandler->errorLog), 1763000009);
        }
    }

    private function createQueryBuilder(): QueryBuilder
    {
        $queryBuilder = $this->connectionPool->getQueryBuilderForTable(self::TABLE);
        $queryBuilder->getRestrictions()
            ->removeAll()
            ->add(GeneralUtility::makeInstance(DeletedRestriction::class))
            ->add(GeneralUtility::makeInstance(WorkspaceRestriction::class, 0));
        return $queryBuilder;
    }

    /**
     * @param array<int, string|int> $arguments
     */
    private function translate(string $key, array $arguments = []): string
    {
        $label = $this->getLanguageService()->sL(self::LANGUAGE_FILE . $key);
        return $arguments === [] ? $label : vsprintf($label, $arguments);
    }

    private function getLanguageService(): LanguageService
    {
        return $GLOBALS['LANG'] ?? $this->languageServiceFactory->createFromUserPreferences($this->getBackendUser());
    }

    private function getBackendUser(): BackendUserAuthentication
    {
        return $GLOBALS['BE_USER'];
    }
}
