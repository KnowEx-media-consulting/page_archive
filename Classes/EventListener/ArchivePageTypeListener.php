<?php

declare(strict_types=1);

namespace Knowex\PageArchive\EventListener;

use Knowex\PageArchive\Service\ArchiveService;
use TYPO3\CMS\Core\Attribute\AsEventListener;
use TYPO3\CMS\Core\Configuration\Event\AfterTcaCompilationEvent;

/**
 * Archivierte Seiten zeigen im Formular dieselben Felder wie Standardseiten. Das passiert erst nach dem
 * Laden aller TCA-Overrides, damit auch Felder anderer Extensions enthalten sind.
 */
final class ArchivePageTypeListener
{
    #[AsEventListener('page-archive/archive-page-type')]
    public function __invoke(AfterTcaCompilationEvent $event): void
    {
        $tca = $event->getTca();
        if (isset($tca['pages']['types']['1'])) {
            $tca['pages']['types'][(string)ArchiveService::DOKTYPE_ARCHIVE] = $tca['pages']['types']['1'];
            $event->setTca($tca);
        }
    }
}
