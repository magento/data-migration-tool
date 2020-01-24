2.3.4
=============
* Added ability for Data Migration Tool to load group-xml files outside its own directory   
* Added support for versions:

   * Magento Open Source: 2.3.4, 1.9.4.4
   * Magento Commerce: 2.3.4, 1.14.4.4

* Fixed bugs:

   * Error during migration to Magento Commerce when Staging module not installed   
   * Error documents are not mapped catalog_category_flat when migrating Magento 1 with flat tables
   * Multiple queries can't be executed error when add several SQL init statements before Magento 1 store migration
   * If product_url_suffix is null then it slows down migration
   * Dropdowns Missing On Frontend After Migration
   * Empty categories after migration
   * No necessary product index tables are migrated
   * Mismatch of entities in the document warnings with the tables which are not part of delta migration
   * Volume check throws Warnings on Delta migration for catalog tables when products with multiselect attribute were changed on Magento 1
   * Volume checks are not run on Delta without
   * Error Base table or view not found when using prefix in Magento 2 DB tables
   * [Issue #727](https://github.com/magento/data-migration-tool/issues/727): Admin URL not working after migration
   * [Issue #731](https://github.com/magento/data-migration-tool/issues/731): Next order increment Id duplicates for different store view after migratio
   * [Issue #738](https://github.com/magento/data-migration-tool/issues/738): Load test classes from autoload-dev block
   * [Issue #746](https://github.com/magento/data-migration-tool/issues/746): Error array_key_exists() expects parameter 2 to be array on Map step
   * [Issue #760](https://github.com/magento/data-migration-tool/issues/760): More info about record field
   * [Issue #775](https://github.com/magento/data-migration-tool/issues/775): Delta rewrite URL CE step not using database prefix correctly

2.3.3
=============
* Improvements in Delta migration. Now it can migrate new or changed in admin panel products and categories
* Added ability for Data Migration Tool to load map files outside its own directory   
* Added support for versions:

   * Magento Open Source: 2.3.3, 1.9.4.3
   * Magento Commerce: 2.3.3, 1.14.4.3

* Fixed bugs:

   * Url rewrites duplicates resoled incorrect with several websites   
   * Generation of URN in PhpStorm does not work for Data Migration Tool
   * Error during migration from text type table to varchar type table when no custom multiselect attribute data exist
   * Target Rules throws error on Delta migration
   * Error message was hidden by progress bar during migration in UrlRewrites step 
   * [Issue #646](https://github.com/magento/data-migration-tool/issues/646): Migrated products with multiselect attribute do not show in layered navigation
   * [Issue #659](https://github.com/magento/data-migration-tool/issues/659): Error duplicate entry appears then core_store_group.name has duplicates
   * [Issue #664](https://github.com/magento/data-migration-tool/issues/664): Custom category attributes disappear on category save after migration
   * [Issue #708](https://github.com/magento/data-migration-tool/issues/708): Missing Table Prefix in PostProcessing Step 
   * [Issue #715](https://github.com/magento/data-migration-tool/issues/715): StockSalesChannel does not remove original record from installation prior to insertion of new records 

2.3.2
=============
* Added support for versions:

   * Magento Open Source: 2.3.2, 1.9.4.2 
   * Magento Commerce: 2.3.2, 1.14.4.2

* Fixed bugs:

   * M1 Super Product Attribute to M2 Configuration setting wrong Price
   * [Issue #682](https://github.com/magento/data-migration-tool/issues/682): Incorrect url rewrite record pagination
   * [Issue #685](https://github.com/magento/data-migration-tool/issues/685): Payment method is not available error after migration
   * [Issue #598](https://github.com/magento/data-migration-tool/issues/598): Error in system.log about migrated attribute which is not included into attribute group
   * [Issue #677](https://github.com/magento/data-migration-tool/issues/677): Data migration fails due to invalid regex in widget placeholder handler 

2.3.1
=============
* Added support for versions:

   * Magento Open Source: 2.3.1, 1.9.4.1 
   * Magento Commerce: 2.3.1, 1.14.4.1

* Fixed bugs:

   * Encrypt sensitive data with libsodium
   * [Issue #607](https://github.com/magento/data-migration-tool/issues/607): SalesIncrement Step showed unclear error message
   * [Issue #615](https://github.com/magento/data-migration-tool/issues/615): Next generated increment id was based on the highest increment number from all stores after migration
   * [Issue #574](https://github.com/magento/data-migration-tool/issues/574): Required price field appeared on migrated Grouped products
   * [Issue #235](https://github.com/magento/data-migration-tool/issues/235): EAV step did not revert tables of M2 in case of error
   * [Issue #461](https://github.com/magento/data-migration-tool/issues/461): Virtual classes in Magento 2 caused error on migrating EAV data
   * [Issue #632](https://github.com/magento/data-migration-tool/issues/632): Error when serialized value had false type
   * [Issue #651](https://github.com/magento/data-migration-tool/issues/651): The Data Migration Tool did not notify user when Magento DB uses prefix in tables name

2.3.0
=============
* Added support for versions:

   * Magento Open Source: 1.9.4.0, 2.3.0
   * Magento Commerce: 1.14.4.0, 2.3.0

* Fixed bugs:

   * [Issue #595](https://github.com/magento/data-migration-tool/issues/595): Fields from third-party extensions cause error during migration with `-auto` option
   * [Issue #596](https://github.com/magento/data-migration-tool/issues/596): Error during migration when parent product id is not found in `catalog_product_bundle_option` table
   * [Issue #609](https://github.com/magento/data-migration-tool/issues/609): `Array to string conversion` exception when running Deltas of Sales Order Grid
   * [Issue #201](https://github.com/magento/data-migration-tool/issues/201): Error during migration when there are several attribute sets in Magento 2  

2.2.6
=============
* Added support for versions:

   * Magento Open Source: 1.9.3.10, 2.2.6
   * Magento Commerce: 1.14.3.10, 2.2.6

* Fixed bugs:

   * Duplicate `amazon_customer` record in `map.xml` file
   * [Issue #557](https://github.com/magento/data-migration-tool/issues/557): `map-tier-pricing.xml.dist` references were incorrect
   * [Issue #545](https://github.com/magento/data-migration-tool/issues/545): PayPal standard settings for active/sandbox mode was not carried across
   * [Issue #554](https://github.com/magento/data-migration-tool/issues/554): Duplicate `ignore` mappings found in EE-to-EE platform
   * [Issue #555](https://github.com/magento/data-migration-tool/issues/555): Duplicate `ignore` mappings found in CE-to-EE platform
   * [Issue #556](https://github.com/magento/data-migration-tool/issues/556): Duplicate `ignore` mappings found in CE-to-CE platform
   * [Issue #561](https://github.com/magento/data-migration-tool/issues/561): Incorrect migration of records when value must be moved to a different destination field
   * [Issue #578](https://github.com/magento/data-migration-tool/issues/578): Attributes with dash in attribute codes gave error

2.2.5
=============
* Added support for versions:

   * Magento Open Source: 1.9.3.9, 2.2.5
   * Magento Commerce: 1.14.3.9, 2.2.5

* Fixed bugs:

   * New table `tablevertex_order_invoice_status` caused error during migration
   * Image excluding and sort order did not migrate properly on Magento Commerce
   * [Issue #514](https://github.com/magento/data-migration-tool/issues/514): `base_grand_total` field was not migrated in invoice grid table
   * [Issue #534](https://github.com/magento/data-migration-tool/issues/534): The migration step Log did not update the progress bar with large data
   * [Issue #535](https://github.com/magento/data-migration-tool/issues/535): B2B destination tables caused errors during migration for Commerce Edition
   * [Issue #536](https://github.com/magento/data-migration-tool/issues/536): Prohibited tabs and format XML files in code of the project
   * [Issue #537](https://github.com/magento/data-migration-tool/issues/537): Comments to the function declaration were missed
   * [Issue #532](https://github.com/magento/data-migration-tool/issues/532): Serialization was used instead of json format for lock file
   * [Issue #533](https://github.com/magento/data-migration-tool/issues/533): Duplicate node was used in mapping files

2.2.4
=============
* Added support for versions:

   * Magento Open Source: 2.2.4
   * Magento Commerce: 2.2.4

* Fixed bugs:

   * New tables `email_abandoned_cart` and `temando_rma_shipment` caused error during migration
   * [Issue #481](https://github.com/magento/data-migration-tool/issues/481): Url rewrite suffix contained only dot symbol
   * [Issue #487](https://github.com/magento/data-migration-tool/issues/487): Important tables were missing in `deltalog.xml.dist`

2.2.3
=============
* Added support for versions:

   * Magento Open Source: 1.9.3.8, 2.2.3
   * Magento Commerce: 1.14.3.8, 2.2.3

* Fixed bugs:

   * Dotmailer Marketing and Temando tables caused errors during migration
   * `Duplicate entry` error on customer_group table when bulk_size was set to 1
   * Error during migration when min cart qty was not serialized
   * CMS pages content was not filtered from 3rd party customizations
   * Prices that were setup per website scope for Configurable products were not migrated properly
   * [Issue #433](https://github.com/magento/data-migration-tool/issues/433): Enterprise gift card accounts did not work in delta mode
   * [Issue #411](https://github.com/magento/data-migration-tool/issues/411): CMS Pages caused errors if contained XML layout code
   * [Issue #445](https://github.com/magento/data-migration-tool/issues/445): Not able to migrate attribute group names if contain non-latin characters 
   * [Issue #454](https://github.com/magento/data-migration-tool/issues/454): Volume check errors were not detailed

2.2.2
=============
* Added support for versions:

   * Magento Open Source: 2.2.2
   * Magento Commerce: 2.2.2

* Fixed bugs:

   * Customer Attribute step did not remember its position
   * Wrong value for `eav_attribute_group.attribute_group_code` field was set for non-product entities
   * [Issue #355](https://github.com/magento/data-migration-tool/issues/355): Data integrity check errors did not indicate the source of the error
   * [Issue #378](https://github.com/magento/data-migration-tool/issues/378): Settings step threw an error when additional fields from an extension were added to the `core_config_data` database table

2.2.1
=============
* Broken serialized data does not stop migration
* Enhancement in supporting PHP 7.1
* Added support for versions:

   * Magento Open Source: 1.9.3.7, 2.2.1
   * Magento Commerce: 1.14.3.7, 2.2.1

* Fixed bugs:

   * Url suffix for products and categories was without dot after migration
   * Redirect loop on Admin Login page if cookie domain was set in store configuration
   * Some serialized settings were not converted into json in Magento Open Source
   * Store step did not support auto resolve function
   * `Array to string conversion error` when product in order did not have options
   * [Issue #400](https://github.com/magento/data-migration-tool/issues/400): Return `$temporaryTable` property to protected visibility

2.2.0
=============
* Integrity check errors can be ignored with a new `-a|--auto` CLI argument
* Customer step was rewritten to be more clear
* Added support for mapping functionality into Stores step
* Added possibility of using secure connection to MySQL
* Added support for PHPUnit 6
* Added support for versions:

   * Magento Open Source: 2.2.0
   * Magento Commerce: 2.2.0

* Fixed bugs:

   * An error on EAV step when records from the `eav_entity_attribute` table have references to non-existent records from the `eav_attribute_set` table

2.1.9
=============
* Added support for versions:

   * Magento CE: 1.9.3.6, 2.1.9
   * Magento EE: 1.14.3.6, 2.1.9

* Fixed bugs:

   * [Issue #351](https://github.com/magento/data-migration-tool/issues/351): An error `Class adminhtml/catalog_product_helper_form_config does not exist` appeared on EAV step during migration 

2.1.8
=============
* Added support for versions:

   * Magento CE: 1.9.3.4, 2.1.8
   * Magento EE: 1.14.3.4, 2.1.8

* Fixed bugs:

   * Volume checks were missing when migrating data to EE
   * Delta did not work on archived sales order grid
   * Urlrewrites added unwanted extra dot to url suffix
   * [Issue #306](https://github.com/magento/data-migration-tool/issues/306): Tables prefix was not added to the table `customer_entity` which triggered error
   * [Issue #279](https://github.com/magento/data-migration-tool/issues/279): No warnings appeared when delta tables could not be created 

2.1.7
=============
* Added support for versions:

   * Magento CE: 1.9.3.3, 2.1.7
   * Magento EE: 1.14.3.3, 2.1.7

* Fixed bugs:
   * [Issue #283](https://github.com/magento/data-migration-tool/issues/283): Ignored attributes were not cleaned properly in the following extended attribute tables: `catalog_eav_attribute`, `customer_eav_attribute`
   * [Issue #176](https://github.com/magento/data-migration-tool/issues/176): Ignored fields in the `map-eav.xml` file caused errors during migration if the table fields could not be NULL (set as NOT NULL)

2.1.6
=============
* Added possibility to specify custom port for MySQL server connection
* Added support for versions:

   * Magento CE: 2.1.6
   * Magento EE: 2.1.6

* Fixed bugs:
   * Unable to save Customer's custom attribute value if the attribute has been created after migration
   * An error occurred during the TierPrice migration step if the product had both Tier and Group Prices and the quantity of a product in Tier Price was 1
   * Issues [#207](https://github.com/magento/data-migration-tool/issues/207), [#264](https://github.com/magento/data-migration-tool/issues/264): Customer Segments with product attributes in conditions caused Store Front to be inoperative
   * [Issue #212](https://github.com/magento/data-migration-tool/issues/212): When some system attributes had not been included in a product attribute set, they were checked and linked to such set instead of being added automatically. These system attributes are: category_ids, giftcard_type, price_type, shipment_type, sku_type, weight_type, swatch_image

2.1.5
=============
* Updated copyright notice
* Added support for versions:

   * Magento CE: 2.1.5
   * Magento EE: 2.1.5

2.1.4
=============
* Added support for versions:

   * Magento CE: 1.9.3.2, 2.1.4
   * Magento EE: 1.14.3.2, 2.1.4

* Fixed bugs:
   * [Issue #81](https://github.com/magento/data-migration-tool/issues/81): URL rewrites were duplicated because of empty URL prefixes from Magento configuration
   * [Issue #203](https://github.com/magento/data-migration-tool/issues/203): SalesIncrement Step (`Delta` migration mode) produced an error when `eav_entity_store` had references to `eav_entity_type` records, added by extensions
   * [Issue #7916](https://github.com/magento/magento2/issues/7916): Saving a category in Magento 2 Admin Panel after migration caused an error when involving products linked to the root category 

2.1.3
=============
* The new DataIntegrity step checks for orphaned records in Magento 1 database
* The TierPrice step now supports data mapping
* Added support for versions:

    * Magento CE: 1.9.3.1, 2.1.3
    * Magento EE: 1.14.3.1, 2.1.3

* Fixed bugs:
    * An error occurred in the TierPrice step while checking structure of database records with the undefined `value_id` field
    * [Issue #123](https://github.com/magento/data-migration-tool/issues/123): After migration, store administrators could not edit details of some products via Admin panel. The error message stated the missing *componentType* configuration parameter for the *design* component
    * [Issue #170](https://github.com/magento/data-migration-tool/issues/170): Migrating orphaned database records caused the `Integrity constraint violation` error in the EAV migration step
    * [Issue #6510](https://github.com/magento/magento2/issues/6510): After migration, editing customer details via Admin panel caused an error message. This happened due to migrating database records with an empty *frontend_input* field (*eav_attribute* table)

2.1.2
=============
* Added support for migration for the following versions:

    * Magento CE version 1.9.3.0, 2.1.2
    * Magento EE version 1.14.3.0, 2.1.2

* Fixed bugs:
    * CMS pages with custom layout were not fully functional after migration
    * Some pages in the Admin Panel could not be displayed due to incorrect redirects
    * [Issue #96](https://github.com/magento/data-migration-tool/issues/96): Incorrect UTF-8 character conversion (<code>?</code> appeared instead of symbols)
    * Issues [#115](https://github.com/magento/data-migration-tool/issues/115), [#159](https://github.com/magento/data-migration-tool/issues/159), [#134](https://github.com/magento/data-migration-tool/issues/134): EAV attributes can now be fully ignored by specifying their entity types
    * [Issue #161](https://github.com/magento/data-migration-tool/issues/161): The <code>advanced/modules_disable_output</code> keys are now ignored by using wildcards (instead of specifying their full names) in the configuration file

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
    * Fixed an issue with migrating passwords
    * [Issue#48](https://github.com/magento/data-migration-tool-ce/issues/48) We no longer migrate backup tables
    * [Issue#15](https://github.com/magento/data-migration-tool-ce/issues/15) Improved error reporting for migrating URL rewrites
    * [Issue#46](https://github.com/magento/data-migration-tool-ce/issues/46) Improved URL rewrite validation
    * [Issue#33](https://github.com/magento/data-migration-tool-ce/issues/33) Resolved issue that resulted in the `inet_ntop(): Invalid in_addr value` error
    * [Issue#12](https://github.com/magento/data-migration-tool-ce/issues/12) Duplicate entries in the `catalogsearch_query` table are no longer migrated
