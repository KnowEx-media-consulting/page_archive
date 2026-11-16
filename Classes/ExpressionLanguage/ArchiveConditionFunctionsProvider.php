<?php

declare(strict_types=1);

namespace Knowex\PageArchive\ExpressionLanguage;

use Symfony\Component\ExpressionLanguage\ExpressionFunction;
use Symfony\Component\ExpressionLanguage\ExpressionFunctionProviderInterface;
use TYPO3\CMS\Core\Authentication\BackendUserAuthentication;

/**
 * Bedingung [pageArchiveHideArchived()] für die User-TSconfig: wahr, solange der Benutzer den Filter
 * "Archiv ausblenden" im Seitenbaum nicht abgeschaltet hat.
 */
final class ArchiveConditionFunctionsProvider implements ExpressionFunctionProviderInterface
{
    public const string USER_SETTING = 'pageArchiveHideArchived';

    /**
     * @return list<ExpressionFunction>
     */
    public function getFunctions(): array
    {
        return [
            new ExpressionFunction(
                'pageArchiveHideArchived',
                static fn() => null,
                static fn(): bool => self::isArchiveHidden()
            ),
        ];
    }

    private static function isArchiveHidden(): bool
    {
        $backendUser = $GLOBALS['BE_USER'] ?? null;
        if (!$backendUser instanceof BackendUserAuthentication) {
            return true;
        }
        // Die User-TSconfig wird geladen, bevor der Core die Einstellungen des Benutzers entpackt.
        $settings = $backendUser->uc
            ?: unserialize((string)($backendUser->user['uc'] ?? ''), ['allowed_classes' => false]);
        return (string)(is_array($settings) ? ($settings[self::USER_SETTING] ?? '1') : '1') !== '0';
    }
}
