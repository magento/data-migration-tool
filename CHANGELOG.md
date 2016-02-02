2.0.2
=============
* Fixed bugs:
    *   There is now one GitHub repository for both the Magento CE and EE migration tools
    * Error messages are more informative
    * Fixed an issue with migrating passwords
    *   [Issue#48](https://github.com/magento/data-migration-tool-ce/issues/48) We no longer migrate backup tables
    *   [Issue#15](https://github.com/magento/data-migration-tool-ce/issues/15) Improved error reporting for migrating URL rewrites
    *   [Issue#46](https://github.com/magento/data-migration-tool-ce/issues/46) Improved URL rewrite validation
    *   [Issue#33](https://github.com/magento/data-migration-tool-ce/issues/33) Resolved issue that resulted in the `inet_ntop(): Invalid in_addr value` error
    *   [Issue#12](https://github.com/magento/data-migration-tool-ce/issues/12) Duplicate entries in the `catalogsearch_query` table are no longer migrated
    *   Added the Magento EE license when migrating
