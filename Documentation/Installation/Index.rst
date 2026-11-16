..  _installation:

============
Installation
============

The extension requires TYPO3 13.4 and PHP 8.3 or higher.

..  rst-class:: bignums

1.  Install the package

    ..  code-block:: bash

        composer require knowex/page-archive

2.  Flush the caches and update the database schema

    The extension adds three fields to the table :sql:`pages`. Flush the caches
    first so that the schema update uses the current TCA.

    ..  code-block:: bash

        vendor/bin/typo3 cache:flush
        vendor/bin/typo3 extension:setup

3.  Give editors the required permissions

    See :ref:`administration-permissions`.

4.  Reload the backend

    The filter in the page tree appears after the backend has been reloaded
    completely.

The extension has no TypoScript, no site set and no extension configuration.
