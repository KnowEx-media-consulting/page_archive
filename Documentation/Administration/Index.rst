..  _administration:

==================
For administrators
==================

..  _administration-permissions:

Permissions
===========

Archiving and restoring run through the DataHandler with the permissions of
the current backend user. Backend user groups of editors need:

*   the page type :guilabel:`Archive` in :guilabel:`Page types`, and the page
    types of the pages they should be able to restore,
*   the fields :guilabel:`Page: Type` and :guilabel:`Page: Disable` in
    :guilabel:`Allowed excludefields`,
*   edit permission on the pages of the branch.

If one page of a branch cannot be edited by the user, nothing is archived or
restored and a message names the page.

..  _administration-filter:

Filter in the page tree
=======================

The extension ships this user TSconfig:

..  code-block:: typoscript
    :caption: EXT:page_archive/Configuration/user.tsconfig

    [pageArchiveHideArchived()]
        options.pageTree.excludeDoktypes := addToList(187)
    [end]

The condition is true as long as the backend user has not switched off
:guilabel:`Hide archive` in the menu of the page tree. To hide archived pages
for a group regardless of the filter, set the option in the TSconfig of that
group without the condition:

..  code-block:: typoscript

    options.pageTree.excludeDoktypes := addToList(187)

..  _administration-command:

Console command
===============

..  code-block:: bash

    # Archive page 123 including all subpages
    vendor/bin/typo3 archive:page 123

    # Restore page 123 including all archived subpages
    vendor/bin/typo3 archive:page 123 --restore

The command runs with the permissions of the command line user.

..  _administration-database:

Database fields
===============

The extension adds these fields to the table :sql:`pages`:

..  t3-field-list-table::
    :header-rows: 1

    -   :Field: Field
        :Description: Description

    -   :Field: tx_pagearchive_original_doktype
        :Description: Page type before archiving

    -   :Field: tx_pagearchive_original_hidden
        :Description: Value of "disabled" before archiving

    -   :Field: tx_pagearchive_archived_at
        :Description: Time of archiving as Unix timestamp

If the page type of a page is set to :guilabel:`Archive` by hand in the page
properties, the extension fills these fields and disables the page. Only this
single page is changed, not its subpages.

..  _administration-uninstall:

Uninstalling
============

Restore all archived pages before removing the extension. Without the
extension the page type 187 is unknown to TYPO3, and the stored page types are
lost when the fields are removed from the database.
