..  _introduction:

============
Introduction
============

..  _what-it-does:

What does it do?
================

The extension adds an archive for pages to the TYPO3 backend.

*   **Archive:** The context menu of the page tree gets the entry
    :guilabel:`Archive`. The page and all its subpages get the page type
    "Archive" and are disabled.
*   **Hidden in the page tree:** Archived pages and their subpages are not
    shown in the page tree of any module.
*   **Filter:** The menu of the page tree gets the entry
    :guilabel:`Hide archive`. Switching it off shows the archived pages at
    their original position, marked with the archive icon.
*   **Restore:** The context menu of an archived page offers
    :guilabel:`Restore` instead of :guilabel:`Archive`. The whole branch gets
    back the page types and the visibility it had before.
*   **Command line:** Pages can be archived and restored with a console
    command.

..  _how-it-works:

How does it work?
=================

Archiving does not move or copy anything. For each page of the branch the
extension

*   stores the current page type and the current value of "disabled" in the
    page record,
*   sets the page type to "Archive" (doktype 187),
*   disables the page.

Restoring writes the stored values back. Content elements, page properties,
slugs and the position in the page tree are not touched at any time.

The page tree hides the page type "Archive" with the user TSconfig option
:typoscript:`options.pageTree.excludeDoktypes`. The option is set inside a
condition that reads the filter setting of the current backend user.
