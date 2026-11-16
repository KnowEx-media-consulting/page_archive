<?php

declare(strict_types=1);

use Knowex\PageArchive\Controller\ArchiveController;

return [
    'pagearchive_archive' => [
        'path' => '/page-archive/archive',
        'methods' => ['POST'],
        'target' => ArchiveController::class . '::archiveAction',
    ],
    'pagearchive_restore' => [
        'path' => '/page-archive/restore',
        'methods' => ['POST'],
        'target' => ArchiveController::class . '::restoreAction',
    ],
];
