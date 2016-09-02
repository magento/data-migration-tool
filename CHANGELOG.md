2.1.1
=============
* Improvements in migration of Magento 1 CE stores upgraded to 1.6 and later versions from versions earlier than 1.6 version
* Performance improvements in migration process
* Added support for migration for the following versions:

    * Magento CE version 2.1.1
    * Magento EE version 2.1.1

* Fixed bugs:
    * Fixed an issue with URL Rewrite duplication in CMS Pages and Catalog
    * [Issue#112](https://github.com/magento/data-migration-tool/issues/112) Errors on EAV step
    * [Issue#75](https://github.com/magento/data-migration-tool/issues/75) Data Migration hung at Customer Attributes Step
    * [Issue#64](https://github.com/magento/data-migration-tool/issues/64) Migration does not run on PHP 7 after DI compilation

2.1.0
=============
* Added support for migration of Magento 1 CE stores upgraded to 1.6 and later versions from versions earlier than 1.6 version
* Added support for migration for the following versions:

    * Magento CE v. 1.9.2.4, v. 2.1.0
    * Magento EE v. 1.14.2.4, v.2.1.0

* Fixed bugs:
    * Fixed an issue with RMA creation after migration
    * Fixed an issue with URL Rewrite duplication for CMS Pages
    * [Issue#59](https://github.com/magento/data-migration-tool/issues/59) Wrong URL addresses for products and categories
    * [Issue#36](https://github.com/magento/data-migration-tool/issues/36) `Incorrect table name` error on products grid page

2.0.7
=============
* Added support for:

    * Magento CE version 2.0.7
    * Magento EE version 2.0.7

2.0.5
=============
* Added support for:

    * Magento CE version 2.0.5
    * Magento EE version 2.0.5

2.0.2
=============
* There is now one GitHub repository for both the Magento CE and EE migration tools
* Added the Magento EE license when migrating
* Error messages are more informative
* Added support for:

    * Magento CE versions 1.9.2.3, 2.0.2
    * Magento EE version 1.14.2.3, 2.0.2

* Fixed bugs:
    *	Fixed an issue with migrating passwords
    *   [Issue#48](https://github.com/magento/data-migration-tool-ce/issues/48) We no longer migrate backup tables
    *   [Issue#15](https://github.com/magento/data-migration-tool-ce/issues/15) Improved error reporting for migrating URL rewrites
    *   [Issue#46](https://github.com/magento/data-migration-tool-ce/issues/46) Improved URL rewrite validation
    *   [Issue#33](https://github.com/magento/data-migration-tool-ce/issues/33) Resolved issue that resulted in the `inet_ntop(): Invalid in_addr value` error
    *   [Issue#12](https://github.com/magento/data-migration-tool-ce/issues/12) Duplicate entries in the `catalogsearch_query` table are no longer migrated

