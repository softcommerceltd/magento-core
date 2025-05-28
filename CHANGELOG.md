## Changelog

### Version 1.6.1
- **Enhancement**: Enhanced Configurable-Product Detection in Export/Import Interface [#64]

### Version 1.6.0
- **Compatibility**: Added compatibility for Magento 2.4.8

### Version 1.5.9
- **Enhancement**: Add Method to FileImageManagement Interface for Deleting Downloaded Images from pub/media/import Directory
- **Enhancement**: Add Weight Unit Source Options to Enable Configuration via UI Profiles

### Version 1.5.8
- **Compatibility**: Added compatibility for PHP 8.4 and Magento 2.4.8-beta

### Version 1.5.7
- **Enhancement**: Added console log debugging in admin area for JS modules

### Version 1.5.6
- **Enhancement**: Added an option to display installed metapackages for the bundled modules

### Version 1.5.5
- **Compatibility**: Add compatibility for Magento 2.4.7-p2

### Version 1.5.4
- **Fix**: Cannot use "String" in namespace as it is reserved since PHP 7 [#13]

### Version 1.5.3
- **Enhancement**: Introduce tooltip renderer for UI columns [#12]
- **Enhancement**: Introduce flattening array interface [#11]

### Version 1.5.2
- **Fix**: Applied a fix to \SoftCommerce\Core\Model\Store\WebsiteStorage::getStoreIdToWebsiteId method where argument data type for $storeId was changed from string to an integer [#10].

### Version 1.5.1
- **Feature**: Added service & processor interface wrapper for dependant modules that use data processing.

### Version 1.5.0
- **Compatibility**: Introduced compatibility with PHP type declaration [#9]
- **Compatibility**: Introduced support for PHP 8.3 [#8]
- **Feature**: Implement functionality to support UI form scope data [#7]

### Version 1.4.6
- **Fix**: TypeError returned at vendor/softcommerce/module-core/Model/Eav/GetEavAttributeOptionValueData.php:170. https://github.com/softcommerceltd/mage2plenty-os/issues/13: [#13]

### Version 1.4.5
- **Enhancement**: Compatibility with magento commerce staging [#6]

### Version 1.4.4
- **Enhancement**: New method to build DB metadata for insert request. [#5]

### Version 1.4.3
- **Enhancement**: An option to retrieve attribute source options in \SoftCommerce\Core\Model\Eav\GetEavAttributeOptionValueData [#4]

### Version 1.4.2
- **Enhancement**: Improvements to data storage framework [#3]

### Version 1.4.1
- **Enhancement**: Add new methods to `\SoftCommerce\Core\Model\Catalog\MediaManagementInterface`

### Version 1.4.0
- **Compatibility**: Add compatibility for Magento 2.4.6-p3 and Magento 2.4.7
- **Feature**: Improvements and new functionality [#2]

### Version 1.3.7
- **Enhancement**: Add a custom field heading for system configuration. [#1]

### Version 1.3.6
- **Compatibility**: Add compatibility for PHP 8.2 and Magento 2.4.6-p1 #2

### Version 1.3.5
- **Feature**: Added new CLI to clean up the static view files from `pub/static` and `var/vew_processed` directories.
- **Compatibility**: Compatibility with Magento 2.4.6 [CE|EC|ECE].

### Version 1.3.4
- **Enhancement**: Added improvements and compatibility with php 8.[0.1] to `SoftCommerce\Core\Framework\DataStorageInterface`.

### Version 1.3.3
- **Enhancement**: Added json content renderer to UI listing columns component.

### Version 1.3.2
- **Enhancement**: Minor improvements to DataStorage functionality.

### Version 1.3.1
- **Enhancement**: Added an option to SKU storage to retrieve data by `entity_id`.

### Version 1.3.0
- **Enhancement**: Added an option to provide custom database columns in `SoftCommerce\Core\Model\Utils\SkuStorageInterface`
model that's used to retrieve product entity data in array format.

### Version 1.2.9
- **Fix**: Applied a fix to license compatibility.

### Version 1.2.8
- **Compatibility**: Compatibility with Magento [OS/AC] 2.4.5 and PHP 8

### Version 1.2.7
- **Enhancement**: Added option to store multidimensional data to `SoftCommerce\Core\Framework\DataStorageInterface::setData`.

### Version 1.2.6
- **Enhancement**: Improvements to `SkuStorage` functionality.

### Version 1.2.5
- **Enhancement**: Improvements to ACL rules.

### Version 1.2.4
- **Compatibility**: Compatibility with Magento Extension Quality Program (EQP).

### Version 1.2.3
- **Fix**: JQMIGRATE: HTML tags must be properly nested and closed.

### Version 1.2.2
- **Enhancement**: Changes to PDT.

### Version 1.2.1
- **Enhancement**: [M2P-7] Add support for sequence entity ID generation to `\SoftCommerce\Core\Model\Utils\GetEntityMetadata`.

### Version 1.2.0
- **Compatibility**: Compatibility with Magento Open Source 2.4.4 [#4]

### Version 1.0.3
- **Feature**: New module to handle Log services. [#3]
- **Compatibility**: Compatibility with Magento Open Source 2.3.5 - 2.4.3 [#2]
- **Enhancement**: Integration Tests [#1]

### Version 1.0.2
- **Feature**: Added new entity data storage for request and response queries.
- **Compatibility**: Compatibility with removed \Laminas\Log\Loger package.

### Version 1.0.1
- **Feature**: Added data storage including output filters to framework.

### Version 1.0.0
- **Feature**: [SCMC-1] New module to global functionality for dependant modules.
