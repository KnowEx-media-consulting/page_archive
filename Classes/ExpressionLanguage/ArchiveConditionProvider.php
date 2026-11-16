<?php

declare(strict_types=1);

namespace Knowex\PageArchive\ExpressionLanguage;

use TYPO3\CMS\Core\ExpressionLanguage\AbstractProvider;

final class ArchiveConditionProvider extends AbstractProvider
{
    public function __construct()
    {
        $this->expressionLanguageProviders = [
            ArchiveConditionFunctionsProvider::class,
        ];
    }
}
