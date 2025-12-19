# Changelog

All notable changes to this project will be documented in this file.

The format is based on [Keep a Changelog](https://keepachangelog.com/en/1.0.0/),
and this project adheres to [Semantic Versioning](https://semver.org/spec/v2.0.0.html).

## [Unreleased]

## [2.1.0] - 2025-12-19
### Added
- add CronHeartbeat service for monitoring cron system health

## [2.0.1] - 2025-11-27
### Fixed
- replace union type with mixed for laminas-code compatibility
- remove typed class constants for PHP 8.1/8.2 compatibility

## [2.0.0] - 2025-11-07
### BREAKING CHANGES
- add PHP 8.3/8.4 compatibility with enhanced type safety
- introduce MessageCollector system for centralized message handling
### Added
- enhance StatusInterface with typed constants and new filter methods
- add ConnectionTrait and enhance SkuStorage with EAV lookup
### Fixed
- update email template to HTML format for proper rendering
### Changed
- chore!: remove deprecated Processor architecture

## [1.6.3] - 2025-07-24
### Changed
- The metapackage release notes are now properly extracting and formatting the module changes from the CHANGELOG.md file

## [1.6.2] - 2025-07-13

## [1.6.1] - 2025-07-13
### Changed
- **Enhancement**: Enhanced Configurable-Product Detection in Export/Import Interface [#64]

## [1.6.0] - 2025-07-13
### Changed
- **Compatibility**: Added compatibility for Magento 2.4.8

## [1.5.9] - 2024-07-13
### Changed
- **Enhancement**: Add Method to FileImageManagement Interface for Deleting Downloaded Images from pub/media/import Directory
- **Enhancement**: Add Weight Unit Source Options to Enable Configuration via UI Profiles

## [1.5.8] - 2024-07-13
### Changed
- **Compatibility**: Added compatibility for PHP 8.4 and Magento 2.4.8-beta

## [1.5.7] - 2024-07-13
### Changed
- **Enhancement**: Added console log debugging in admin area for JS modules

## [1.5.6] - 2024-07-13
### Changed
- **Enhancement**: Added an option to display installed metapackages for the bundled modules

## [1.5.5] - 2024-07-13
### Changed
- **Compatibility**: Add compatibility for Magento 2.4.7-p2

## [1.5.4] - 2024-09-21
### Fixed
- Cannot use "String" in namespace as it is reserved since PHP 7 [#13]

## [1.5.3] - 2024-09-13
### Changed
- **Enhancement**: Introduce tooltip renderer for UI columns [#12]
- **Enhancement**: Introduce flattening array interface [#11]

## [1.5.2] - 2024-07-03
### Fixed
- Applied a fix to \SoftCommerce\Core\Model\Store\WebsiteStorage::getStoreIdToWebsiteId method where argument data type for $storeId was changed from string to an integer [#10].

## [1.5.1] - 2024-05-18
### Added
- Added service & processor interface wrapper for dependant modules that use data processing.

## [1.5.0] - 2024-03-21
### Added
- Implement functionality to support UI form scope data [#7]
### Changed
- **Compatibility**: Introduced compatibility with PHP type declaration [#9]
- **Compatibility**: Introduced support for PHP 8.3 [#8]

## [1.4.6] - 2024-02-19
### Fixed
- TypeError returned at vendor/softcommerce/module-core/Model/Eav/GetEavAttributeOptionValueData.php:170. https://github.com/softcommerceltd/mage2plenty-os/issues/13: [#13]

## [1.4.5] - 2024-02-16
### Changed
- **Enhancement**: Compatibility with magento commerce staging [#6]

## [1.4.4] - 2024-02-01
### Changed
- **Enhancement**: New method to build DB metadata for insert request. [#5]

## [1.4.3] - 2024-01-07
### Changed
- **Enhancement**: An option to retrieve attribute source options in \SoftCommerce\Core\Model\Eav\GetEavAttributeOptionValueData [#4]

## [1.4.2] - 2023-12-11
### Changed
- **Enhancement**: Improvements to data storage framework [#3]

## [1.4.1] - 2023-12-01
### Changed
- **Enhancement**: Add new methods to `\SoftCommerce\Core\Model\Catalog\MediaManagementInterface`

## [1.4.0] - 2023-11-30
### Added
- Improvements and new functionality [#2]
### Changed
- **Compatibility**: Add compatibility for Magento 2.4.6-p3 and Magento 2.4.7

## [1.3.7] - 2023-08-07
### Changed
- **Enhancement**: Add a custom field heading for system configuration. [#1]

## [1.3.6] - 2023-06-24
### Changed
- **Compatibility**: Add compatibility for PHP 8.2 and Magento 2.4.6-p1 #2

## [1.3.5] - 2023-03-16
### Added
- Added new CLI to clean up the static view files from `pub/static` and `var/vew_processed` directories.
### Changed
- **Compatibility**: Compatibility with Magento 2.4.6 [CE|EC|ECE].

## [1.3.4] - 2023-03-13
### Changed
- **Enhancement**: Added improvements and compatibility with php 8.[0.1] to `SoftCommerce\Core\Framework\DataStorageInterface`.

## [1.3.3] - 2023-01-03
### Changed
- **Enhancement**: Added json content renderer to UI listing columns component.

## [1.3.2] - 2022-12-31
### Changed
- **Enhancement**: Minor improvements to DataStorage functionality.

## [1.3.1] - 2022-12-05
### Changed
- **Enhancement**: Added an option to SKU storage to retrieve data by `entity_id`.

## [1.3.0] - 2022-11-29
### Changed
- **Enhancement**: Added an option to provide custom database columns in `SoftCommerce\Core\Model\Utils\SkuStorageInterface`

## [1.2.9] - 2022-11-28
### Fixed
- Applied a fix to license compatibility.

## [1.2.8] - 2022-11-09
### Changed
- **Compatibility**: Compatibility with Magento [OS/AC] 2.4.5 and PHP 8

## [1.2.7] - 2022-11-02
### Changed
- **Enhancement**: Added option to store multidimensional data to `SoftCommerce\Core\Framework\DataStorageInterface::setData`.

## [1.2.6] - 2022-08-24
### Changed
- **Enhancement**: Improvements to `SkuStorage` functionality.

## [1.2.5] - 2022-08-16
### Changed
- **Enhancement**: Improvements to ACL rules.

## [1.2.4] - 2022-07-22
### Changed
- **Compatibility**: Compatibility with Magento Extension Quality Program (EQP).

## [1.2.3] - 2022-07-15
### Fixed
- JQMIGRATE: HTML tags must be properly nested and closed.

## [1.2.2] - 2022-07-03
### Changed
- **Enhancement**: Changes to PDT.

## [1.2.1] - 2022-06-11
### Changed
- **Enhancement**: [M2P-7] Add support for sequence entity ID generation to `\SoftCommerce\Core\Model\Utils\GetEntityMetadata`.

## [1.2.0] - 2022-06-08
### Changed
- **Compatibility**: Compatibility with Magento Open Source 2.4.4 [#4]

## [1.0.3] - 2022-06-11
### Added
- New module to handle Log services. [#3]
### Changed
- **Compatibility**: Compatibility with Magento Open Source 2.3.5 - 2.4.3 [#2]
- **Enhancement**: Integration Tests [#1]

## [1.0.2] - 2022-06-10
### Added
- Added new entity data storage for request and response queries.
### Changed
- **Compatibility**: Compatibility with removed \Laminas\Log\Loger package.

## [1.0.1] - 2022-05-16
### Added
- Added data storage including output filters to framework.

## [1.0.0] - 2023-09-13
### Added
- [SCMC-1] New module to global functionality for dependant modules.

[Unreleased]: https://github.com/softcommerceltd/magento-core/compare/v1.6.1...HEAD
[1.6.1]: https://github.com/softcommerceltd/magento-core/compare/v1.6.0...v1.6.1
[1.6.0]: https://github.com/softcommerceltd/magento-core/compare/v1.5.9...v1.6.0
[1.5.9]: https://github.com/softcommerceltd/magento-core/compare/v1.5.8...v1.5.9
[1.5.8]: https://github.com/softcommerceltd/magento-core/compare/v1.5.7...v1.5.8
[1.5.7]: https://github.com/softcommerceltd/magento-core/compare/v1.5.6...v1.5.7
[1.5.6]: https://github.com/softcommerceltd/magento-core/compare/v1.5.5...v1.5.6
[1.5.5]: https://github.com/softcommerceltd/magento-core/compare/v1.5.4...v1.5.5
[1.5.4]: https://github.com/softcommerceltd/magento-core/compare/v1.5.3...v1.5.4
[1.5.3]: https://github.com/softcommerceltd/magento-core/compare/v1.5.2...v1.5.3
[1.5.2]: https://github.com/softcommerceltd/magento-core/compare/v1.5.1...v1.5.2
[1.5.1]: https://github.com/softcommerceltd/magento-core/compare/v1.5.0...v1.5.1
[1.5.0]: https://github.com/softcommerceltd/magento-core/compare/v1.4.6...v1.5.0
[1.4.6]: https://github.com/softcommerceltd/magento-core/compare/v1.4.5...v1.4.6
[1.4.5]: https://github.com/softcommerceltd/magento-core/compare/v1.4.4...v1.4.5
[1.4.4]: https://github.com/softcommerceltd/magento-core/compare/v1.4.3...v1.4.4
[1.4.3]: https://github.com/softcommerceltd/magento-core/compare/v1.4.2...v1.4.3
[1.4.2]: https://github.com/softcommerceltd/magento-core/compare/v1.4.1...v1.4.2
[1.4.1]: https://github.com/softcommerceltd/magento-core/compare/v1.4.0...v1.4.1
[1.4.0]: https://github.com/softcommerceltd/magento-core/compare/v1.3.7...v1.4.0
[1.3.7]: https://github.com/softcommerceltd/magento-core/compare/v1.3.6...v1.3.7
[1.3.6]: https://github.com/softcommerceltd/magento-core/compare/v1.3.5...v1.3.6
[1.3.5]: https://github.com/softcommerceltd/magento-core/compare/v1.3.4...v1.3.5
[1.3.4]: https://github.com/softcommerceltd/magento-core/compare/v1.3.3...v1.3.4
[1.3.3]: https://github.com/softcommerceltd/magento-core/compare/v1.3.2...v1.3.3
[1.3.2]: https://github.com/softcommerceltd/magento-core/compare/v1.3.1...v1.3.2
[1.3.1]: https://github.com/softcommerceltd/magento-core/compare/v1.3.0...v1.3.1
[1.3.0]: https://github.com/softcommerceltd/magento-core/compare/v1.2.9...v1.3.0
[1.2.9]: https://github.com/softcommerceltd/magento-core/compare/v1.2.8...v1.2.9
[1.2.8]: https://github.com/softcommerceltd/magento-core/compare/v1.2.7...v1.2.8
[1.2.7]: https://github.com/softcommerceltd/magento-core/compare/v1.2.6...v1.2.7
[1.2.6]: https://github.com/softcommerceltd/magento-core/compare/v1.2.5...v1.2.6
[1.2.5]: https://github.com/softcommerceltd/magento-core/compare/v1.2.4...v1.2.5
[1.2.4]: https://github.com/softcommerceltd/magento-core/compare/v1.2.3...v1.2.4
[1.2.3]: https://github.com/softcommerceltd/magento-core/compare/v1.2.2...v1.2.3
[1.2.2]: https://github.com/softcommerceltd/magento-core/compare/v1.2.1...v1.2.2
[1.2.1]: https://github.com/softcommerceltd/magento-core/compare/v1.2.0...v1.2.1
[1.2.0]: https://github.com/softcommerceltd/magento-core/compare/v1.0.3...v1.2.0
[1.0.3]: https://github.com/softcommerceltd/magento-core/compare/v1.0.2...v1.0.3
[1.0.2]: https://github.com/softcommerceltd/magento-core/compare/v1.0.1...v1.0.2
[1.0.1]: https://github.com/softcommerceltd/magento-core/compare/v1.0.0...v1.0.1
[1.0.0]: https://github.com/softcommerceltd/magento-core/releases/tag/v1.0.0
