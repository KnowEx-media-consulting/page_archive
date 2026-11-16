<?php

declare(strict_types=1);

use Knowex\PageArchive\Service\ArchiveService;
use TYPO3\CMS\Core\DataHandling\PageDoktypeRegistry;
use TYPO3\CMS\Core\Utility\GeneralUtility;

defined('TYPO3') or die();

// Archivierte Seiten dürfen alle Datensätze behalten, die vorher auf ihnen lagen (z. B. aus Ordnern).
GeneralUtility::makeInstance(PageDoktypeRegistry::class)->add(
    ArchiveService::DOKTYPE_ARCHIVE,
    ['allowedTables' => '*']
);
