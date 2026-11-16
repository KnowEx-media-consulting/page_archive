<?php

declare(strict_types=1);

namespace Knowex\PageArchive\ContextMenu;

use Knowex\PageArchive\Service\ArchiveService;
use TYPO3\CMS\Backend\ContextMenu\ItemProviders\AbstractProvider;
use TYPO3\CMS\Backend\Routing\UriBuilder;
use TYPO3\CMS\Backend\Utility\BackendUtility;
use TYPO3\CMS\Core\Type\Bitmask\Permission;
use TYPO3\CMS\Core\Utility\GeneralUtility;

/**
 * Ergänzt das Kontextmenü des Seitenbaums um "Archivieren".
 */
final class ArchiveItemProvider extends AbstractProvider
{
    private const string LANGUAGE_FILE = 'LLL:EXT:page_archive/Resources/Private/Language/locallang.xlf:';

    /**
     * @var array<string, array<string, string>>
     */
    protected $itemsConfiguration = [
        'pageArchive' => [
            'type' => 'item',
            'label' => self::LANGUAGE_FILE . 'contextmenu.archive',
            'iconIdentifier' => 'page-archive-page',
            'callbackAction' => 'archivePage',
        ],
        'pageRestore' => [
            'type' => 'item',
            'label' => self::LANGUAGE_FILE . 'action.restore',
            'iconIdentifier' => 'actions-edit-restore',
            'callbackAction' => 'restorePage',
        ],
    ];

    /**
     * @var array<string, mixed>|null
     */
    private ?array $page = null;

    public function canHandle(): bool
    {
        return $this->table === 'pages' && (int)$this->identifier > 0;
    }

    /**
     * Niedriger als der PageProvider (100), damit dessen Einträge schon vorhanden sind.
     */
    public function getPriority(): int
    {
        return 55;
    }

    /**
     * @param array<string, mixed> $items
     * @return array<string, mixed>
     */
    public function addItems(array $items): array
    {
        $this->initDisabledItems();
        $this->page = BackendUtility::getRecord('pages', (int)$this->identifier);
        if ($this->isArchived()) {
            // Sichtbar wird eine archivierte Seite nur über "Wiederherstellen".
            unset($items['enable'], $items['disable']);
        }
        $localItems = $this->prepareItems($this->itemsConfiguration);
        if ($localItems === []) {
            return $items;
        }
        // Direkt vor "Löschen" einsortieren, sonst ans Ende.
        $position = array_search('delete', array_keys($items), true);
        if ($position === false) {
            return $items + $localItems;
        }
        return array_slice($items, 0, $position, true) + $localItems + array_slice($items, $position, null, true);
    }

    protected function canRender(string $itemName, string $type): bool
    {
        if (in_array($itemName, $this->disabledItems, true) || $this->page === null) {
            return false;
        }
        if (
            (int)$this->page['sys_language_uid'] !== 0
            || !$this->backendUser->doesUserHaveAccess($this->page, Permission::PAGE_EDIT)
        ) {
            return false;
        }
        return match ($itemName) {
            'pageArchive' => !$this->isArchived() && (int)$this->page['is_siteroot'] !== 1,
            'pageRestore' => $this->isArchived(),
            default => false,
        };
    }

    private function isArchived(): bool
    {
        return (int)($this->page['doktype'] ?? 0) === ArchiveService::DOKTYPE_ARCHIVE;
    }

    /**
     * @return array<string, string>
     */
    protected function getAdditionalAttributes(string $itemName): array
    {
        $restore = $itemName === 'pageRestore';
        $prefix = $restore ? 'contextmenu.restore.' : 'contextmenu.confirm.';
        return [
            'data-callback-module' => '@knowex/page-archive/context-menu-actions',
            'data-action-url' => (string)GeneralUtility::makeInstance(UriBuilder::class)
                ->buildUriFromRoute($restore ? 'ajax_pagearchive_restore' : 'ajax_pagearchive_archive'),
            'data-title' => $this->languageService->sL(self::LANGUAGE_FILE . $prefix . 'title'),
            'data-message' => sprintf(
                $this->languageService->sL(self::LANGUAGE_FILE . $prefix . 'message'),
                (string)($this->page['title'] ?? '')
            ),
            'data-button-ok-text' => $this->languageService->sL(
                self::LANGUAGE_FILE . ($restore ? 'action.restore' : 'contextmenu.archive')
            ),
            'data-button-close-text' => $this->languageService->sL(self::LANGUAGE_FILE . 'button.cancel'),
            'data-error-text' => $this->languageService->sL(self::LANGUAGE_FILE . 'error.generic'),
        ];
    }

    protected function getIdentifier(): string
    {
        return $this->identifier;
    }
}
