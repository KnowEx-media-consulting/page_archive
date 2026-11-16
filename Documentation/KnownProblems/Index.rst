..  _known-problems:

==============
Known problems
==============

*   **Links to archived pages:** Shortcuts, "show content from page" and links
    that point to an archived page lead nowhere while the page is archived.
    The extension does not check references before archiving.
*   **Translations:** Only pages in the default language are archived and
    restored. A disabled default language page also makes its translations
    unavailable, but the translated records keep their own page type.
*   **Workspaces:** Archiving and restoring work on live pages only.
*   **Page type 187:** The number of the page type is fixed. It conflicts with
    other extensions that register the same number.
*   **TYPO3 14:** The filter entry is added to the menu of the core page tree
    by JavaScript. This has to be checked when TYPO3 changes the page tree.
