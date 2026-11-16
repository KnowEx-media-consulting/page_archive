<?php

declare(strict_types=1);

namespace Knowex\PageArchive\Hooks;

use Knowex\PageArchive\Service\ArchiveService;
use TYPO3\CMS\Backend\Utility\BackendUtility;
use TYPO3\CMS\Core\DataHandling\DataHandler;
use TYPO3\CMS\Core\Utility\MathUtility;

/**
 * Stellt jemand den Seitentyp im Formular von Hand auf "Archiv" (oder zurück), werden die Archiv-Felder
 * so gesetzt, als wäre die Seite über das Kontextmenü archiviert bzw. über das Modul wiederhergestellt worden.
 */
final class DataHandlerHook
{
    /**
     * @param array<string, mixed> $fieldArray
     */
    public function processDatamap_preProcessFieldArray(
        array &$fieldArray,
        string $table,
        string|int $id,
        DataHandler $dataHandler
    ): void {
        if ($table !== 'pages' || !isset($fieldArray['doktype']) || !MathUtility::canBeInterpretedAsInteger($id)) {
            return;
        }
        // Der ArchiveService setzt die Felder selbst.
        if (array_key_exists('tx_pagearchive_archived_at', $fieldArray)) {
            return;
        }
        $page = BackendUtility::getRecord('pages', (int)$id, 'doktype,hidden');
        if ($page === null) {
            return;
        }

        $wasArchived = (int)$page['doktype'] === ArchiveService::DOKTYPE_ARCHIVE;
        $isArchived = (int)$fieldArray['doktype'] === ArchiveService::DOKTYPE_ARCHIVE;
        if ($isArchived && !$wasArchived) {
            $fieldArray['hidden'] = 1;
            $fieldArray['tx_pagearchive_original_doktype'] = (int)$page['doktype'];
            $fieldArray['tx_pagearchive_original_hidden'] = (int)$page['hidden'];
            $fieldArray['tx_pagearchive_archived_at'] = (int)$GLOBALS['EXEC_TIME'];
        } elseif (!$isArchived && $wasArchived) {
            $fieldArray['tx_pagearchive_original_doktype'] = 0;
            $fieldArray['tx_pagearchive_original_hidden'] = 0;
            $fieldArray['tx_pagearchive_archived_at'] = 0;
        }
    }
}
