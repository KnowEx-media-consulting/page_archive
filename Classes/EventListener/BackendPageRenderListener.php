<?php

declare(strict_types=1);

namespace Knowex\PageArchive\EventListener;

use TYPO3\CMS\Backend\Controller\Event\AfterBackendPageRenderEvent;
use TYPO3\CMS\Core\Attribute\AsEventListener;
use TYPO3\CMS\Core\Localization\LanguageService;
use TYPO3\CMS\Core\Page\JavaScriptModuleInstruction;
use TYPO3\CMS\Core\Page\PageRenderer;

/**
 * Lädt im Backend das Skript, das den Filter "Archiv ausblenden" ins Menü des Seitenbaums einhängt.
 */
final class BackendPageRenderListener
{
    public function __construct(private readonly PageRenderer $pageRenderer) {}

    #[AsEventListener('page-archive/page-tree-filter')]
    public function __invoke(AfterBackendPageRenderEvent $event): void
    {
        $this->pageRenderer->addInlineLanguageLabelArray([
            'pageArchive.hideArchived' => $this->getLanguageService()->sL(
                'LLL:EXT:page_archive/Resources/Private/Language/locallang.xlf:tree.hideArchived'
            ),
        ]);
        $this->pageRenderer->getJavaScriptRenderer()->addJavaScriptModuleInstruction(
            JavaScriptModuleInstruction::create('@knowex/page-archive/page-tree-filter.js')
        );
    }

    private function getLanguageService(): LanguageService
    {
        return $GLOBALS['LANG'];
    }
}
