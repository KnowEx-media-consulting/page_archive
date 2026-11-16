<?php

declare(strict_types=1);

use Knowex\PageArchive\Service\ArchiveService;
use TYPO3\CMS\Core\Utility\ExtensionManagementUtility;

defined('TYPO3') or die();

(static function (): void {
    $doktype = (string)ArchiveService::DOKTYPE_ARCHIVE;

    ExtensionManagementUtility::addTcaSelectItem('pages', 'doktype', [
        'label' => 'LLL:EXT:page_archive/Resources/Private/Language/locallang.xlf:doktype.archive',
        'value' => $doktype,
        'icon' => 'page-archive-page',
        'group' => 'special',
    ]);

    $GLOBALS['TCA']['pages']['ctrl']['typeicon_classes'][$doktype] = 'page-archive-page';
    $GLOBALS['TCA']['pages']['ctrl']['typeicon_classes'][$doktype . '-hideinmenu'] = 'page-archive-page';
    $GLOBALS['TCA']['pages']['ctrl']['typeicon_classes'][$doktype . '-root'] = 'page-archive-page';

    // Merken den Zustand vor dem Archivieren, damit sich die Seite unverändert wiederherstellen lässt.
    ExtensionManagementUtility::addTCAcolumns('pages', [
        'tx_pagearchive_original_doktype' => ['config' => ['type' => 'passthrough']],
        'tx_pagearchive_original_hidden' => ['config' => ['type' => 'passthrough']],
        'tx_pagearchive_archived_at' => ['config' => ['type' => 'passthrough']],
    ]);
})();
