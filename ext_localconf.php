<?php

declare(strict_types=1);

use Knowex\PageArchive\Hooks\DataHandlerHook;

defined('TYPO3') or die();

// Hält die Archiv-Felder konsistent, wenn der Seitentyp von Hand im Formular umgestellt wird.
$GLOBALS['TYPO3_CONF_VARS']['SC_OPTIONS']['t3lib/class.t3lib_tcemain.php']['processDatamapClass']['page_archive']
    = DataHandlerHook::class;
