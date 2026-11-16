# page_archive

Archive for TYPO3 pages. Old pages disappear from the backend page tree and from the website without
being deleted, and can be restored with one click.

## What it does

- Adds the page type "Archive" (doktype 187).
- Adds "Archive" to the context menu of the page tree. The page and all its subpages get the page type
  "Archive" and are disabled; type and visibility from before are remembered in the page record.
- Hides archived pages in the page tree of all modules via `options.pageTree.excludeDoktypes`
  (user TSconfig). Each user can switch this off with "Hide archive" in the menu of the page tree.
- The context menu of an archived page offers "Restore" instead of "Archive". Restoring brings the
  whole branch back to its previous state.
- CLI: `typo3 archive:page <uid>` and `typo3 archive:page <uid> --restore`.

## Installation

```bash
composer require knowex/page-archive
typo3 cache:flush
typo3 extension:setup
```

## Permissions for editors

Archiving and restoring run through the DataHandler with the rights of the current user. Editor groups need:

- the page type "Archive" (and the page types they want to restore) in "Page types",
- the fields "Page: Type" and "Page: Disable" in "Allowed excludefields",
- edit permission on the pages.

## Notes

- Archived pages are disabled, so they return a 404 in the frontend. Add redirects before archiving
  pages that are still linked from outside.
- The root page of a site cannot be archived.
- A page whose parent page is archived as well can only be restored together with the parent.
