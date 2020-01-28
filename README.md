## Overview
We're pleased you're considering moving from the world's #1 eCommerce platform—Magento 1.x—to the eCommerce platform for the future, Magento 2. We're also excited to share the details about this process, which we refer to as migration.

Magento 2 migration involves four components: data, extensions and custom code, themes, and customizations.

### Data
We've developed the **Magento 2 Data Migration Tool** to help you efficiently move all of your products, customers, and order data, store configurations, promotions and more to Magento 2. See the <a href="https://devdocs.magento.com/guides/v2.3/migration/bk-migration-guide.html" target="_blank">Magento Migration Guide</a> for details.

### Extensions and custom code
We've been working hard with the development community to help you use your Magento 1 extensions in Magento 2. Now we're proud to present the <a href="https://marketplace.magento.com/" target="_blank">Magento Marketplace</a>, where you can download or purchase the latest versions of your favourite extensions.

Also, we have developed the <a href="https://github.com/magento/code-migration" target="_blank">Code Migration Toolkit</a>, which will help to port extensions and your custom code to Magento 2, significantly reducing your porting efforts.

More information on developing extensions for Magento 2 is available in the <a href="http://devdocs.magento.com/guides/v2.3/extension-dev-guide/bk-extension-dev-guide.html" target="_blank">Magento 2 Extension Developer Guide</a>.

### Themes and Customizations
Magento 2 uses new approaches and technologies that give merchants an unmatched ability to create innovative shopping experiences and scale to new levels. To take advantage of these advances, developers will need to make changes to their themes and customizations. Documentation is available online for creating Magento 2 <a href="http://devdocs.magento.com/guides/v2.3/frontend-dev-guide/themes/theme-general.html" target="_blank">themes</a>, <a href="http://devdocs.magento.com/guides/v2.3/frontend-dev-guide/layouts/layout-overview.html" target="_blank">layouts</a>, and <a href="http://devdocs.magento.com/guides/v2.3/frontend-dev-guide/layouts/xml-manage.html" target="_blank">customizations</a>.

### Supported versions
This edition of tool supports the following versions for migration:

*    Magento Open Source version 1.6.x, 1.7.x, 1.8.x, 1.9.x

*    Magento Commerce version 1.11.x, 1.12.x, 1.13.x, 1.14.x

If you migrate from Magento Open Source to Magento Commerce, the following versions are supported:

*    1.6.x, 1.7.x, 1.8.x, 1.9.x

## Prerequisites
Before you start your migration, you must do all of the following:

*    Set up a Magento 2 system that meets our <a href="http://devdocs.magento.com/guides/v2.3/install-gde/system-requirements.html">system requirements</a>.

    Set up your system using a topology and design that at least matches your existing Magento 1.x system.

*    Do not start Magento 2 cron jobs.

*    Back up or <a href="https://dev.mysql.com/doc/refman/5.1/en/mysqldump.html">dump</a> your Magento 2 database as soon after installation as possible.

*    Check that the data migration tool has a network connection to the Magento 1.x and Magento 2 databases.

    Open ports in your firewall so the migration tool can communicate with the databases and so the databases can communicate with each other.

*    Migrate Magento 1.x extension and custom code to Magento 2.

    Reach out to your extension providers to see if they have been ported yet.

## Install the Data Migration Tool
This section discusses how to install the Magento Data Migration Tool. You can install it from either repo.magento.com or from a GitHub repository.

**Note**: The versions of both the migration tool and the Magento 2 code must be identical (for example, 2.3.0). To find the version of either package, open `composer.json` and find the value of `"version"`.

### Install the tool from GitHub
To install the migration tool from GitHub, use the following steps:

1.  Log in to your Magento 2 server as a user with privileges to write to the Magento 2 file system or <a href="http://devdocs.magento.com/guides/v2.3/install-gde/install/prepare-install.html#install-update-depend-apache">switch to the web server user</a>.
2.  Go to Magento 2 root directory.
3.  Enter the following commands:

        composer config repositories.data-migration-tool git https://github.com/magento/data-migration-tool
        composer require magento/data-migration-tool:<version>

    where `<version>` is release version (e.g. 2.3.0)

3.  Wait while dependencies are updated.

### Install the tool from repo.magento.com
To install the Data Migration Tool, you must update `composer.json` in the Magento root installation directory to provide the location of the migration tool package.

To install the migration tool, you must:

1.  Decide the version of `magento/data-migration-tool` you want as discussed in the preceding section.

2.  Run the `composer config` and `composer require` commands to update `composer.json`.

3.  When prompted, enter your <a href="http://devdocs.magento.com/guides/v2.0/install-gde/prereq/connect-auth.html" target="_blank">authentication keys</a>. Your public key is your username; your private key is your password.

To update `composer.json`:

1.  Log in to your Magento server as the <a href="http://devdocs.magento.com/guides/v2.3/install-gde/install/prepare-install.html#install-update-depend-apacheweb">web server user</a> or as a user with `root` privileges.

2.  Change to your Magento installation directory.

3.  Enter the following command to reference Magento packages in `composer.json`:

        composer config repositories.magento composer https://repo.magento.com

4.  Enter the following command to require the current version of the package:

        composer require magento/data-migration-tool:<version>

    where `<version>` is either an exact version or next significant release syntax.

    Exact version example:

        composer require magento/data-migration-tool:2.3.0

    Next significant release example:

        composer require magento/data-migration-tool:~2.3

5.  Wait while dependencies are installed.

## More details
See the <a href="http://devdocs.magento.com/guides/v2.3/migration/bk-migration-guide.html">Migration Guide</a> for the detailed help with your data migration process.