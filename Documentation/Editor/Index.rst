..  _editor:

===========
For editors
===========

..  _editor-archive:

Archive a page
==============

#.  Right-click the page in the page tree.
#.  Choose :guilabel:`Archive` and confirm the dialog.

..  figure:: /Images/ContextMenuArchive.png
    :alt: Context menu of a page in the page tree with the entry "Archive"
    :class: with-shadow

    The entry :guilabel:`Archive` in the context menu of the page tree

..  figure:: /Images/ConfirmArchive.png
    :alt: Dialog asking whether the page and all its subpages should be archived
    :class: with-shadow

    Archiving has to be confirmed

The page and all its subpages disappear from the page tree. They are disabled,
so they are no longer available on the website.

..  attention::

    A page that was visible on the website returns a "page not found" error
    after archiving. If the page is still linked from other websites or from
    search engines, ask an administrator to add a redirect first.

The root page of a site cannot be archived.

..  _editor-show:

Show archived pages
===================

#.  Open the menu of the page tree (the three dots above the tree).
#.  Click :guilabel:`Hide archive` to remove the check mark.

..  figure:: /Images/PageTreeFilter.png
    :alt: Menu of the page tree with the checked entry "Hide archive"
    :class: with-shadow

    The filter :guilabel:`Hide archive` in the menu of the page tree

The page tree now shows the archived pages at their original position, marked
with the archive icon. They can be opened and edited like any other page.
Click :guilabel:`Hide archive` again to hide them.

..  figure:: /Images/PageTreeArchived.png
    :alt: Page tree showing archived pages with the archive icon
    :class: with-shadow

    Archived pages at their original position in the page tree

The setting is stored for your backend user and applies to all modules with a
page tree. It also applies to the page tree in the link dialog.

..  _editor-restore:

Restore a page
==============

#.  Show the archived pages, see :ref:`editor-show`.
#.  Right-click the archived page.
#.  Choose :guilabel:`Restore` and confirm the dialog.

..  figure:: /Images/ContextMenuRestore.png
    :alt: Context menu of an archived page with the entry "Restore"
    :class: with-shadow

    The entry :guilabel:`Restore` in the context menu of an archived page

The page and all its archived subpages get back the page type and the
visibility they had before. Pages that were visible on the website before are
online again.

If the parent page is archived as well, restore the parent page. A single
subpage of an archived branch cannot be restored on its own.
