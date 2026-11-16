<?php

declare(strict_types=1);

namespace Knowex\PageArchive\Controller;

use Knowex\PageArchive\Exception\ArchiveException;
use Knowex\PageArchive\Service\ArchiveService;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use TYPO3\CMS\Backend\Attribute\AsController;
use TYPO3\CMS\Core\Http\JsonResponse;
use TYPO3\CMS\Core\Localization\LanguageService;

/**
 * AJAX-Endpunkte für "Archivieren" und "Wiederherstellen" im Kontextmenü.
 */
#[AsController]
final class ArchiveController
{
    private const string LANGUAGE_FILE = 'LLL:EXT:page_archive/Resources/Private/Language/locallang.xlf:';

    public function __construct(private readonly ArchiveService $archiveService) {}

    public function archiveAction(ServerRequestInterface $request): ResponseInterface
    {
        return $this->process($request, $this->archiveService->archive(...), 'archived');
    }

    public function restoreAction(ServerRequestInterface $request): ResponseInterface
    {
        return $this->process($request, $this->archiveService->restore(...), 'restored');
    }

    /**
     * @param \Closure(int): int $operation
     */
    private function process(ServerRequestInterface $request, \Closure $operation, string $messageKey): ResponseInterface
    {
        $uid = (int)($request->getParsedBody()['uid'] ?? 0);
        try {
            $count = $operation($uid);
        } catch (ArchiveException $exception) {
            return new JsonResponse([
                'success' => false,
                'title' => $this->translate('module.title'),
                'message' => $exception->getMessage(),
            ]);
        }
        return new JsonResponse([
            'success' => true,
            'title' => $this->translate('message.' . $messageKey . '.title'),
            'message' => sprintf($this->translate('message.' . $messageKey), $count),
        ]);
    }

    private function translate(string $key): string
    {
        return $this->getLanguageService()->sL(self::LANGUAGE_FILE . $key);
    }

    private function getLanguageService(): LanguageService
    {
        return $GLOBALS['LANG'];
    }
}
