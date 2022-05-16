# Magento - SoftCommerce Core module
Global Functionalities for bundle modules.

## Requirements
* [Magento 2.3.5 - 2.4.3](https://magento.com/tech-resources/download).
* PHP 7.3.0 or later

## Installation

### Install via composer

Run the following command from Magento root directory:

```
composer config repositories.softcommerce-core vcs https://github.com/softcommerceltd/magento-core.git
composer require softcommerce/module-core
```
If you receive an error regarding php incompatibility, but you are sure your php version is compatible, then use --ignore-platform-reqs
```
composer require softcommerce/module-core --ignore-platform-reqs
```

### Post Installation

In production mode:
```
php bin/magento maintenance:enable
php bin/magento setup:upgrade
php bin/magento deploy:mode:set production
php bin/magento maintenance:disable
```

In development mode:
```
php bin/magento setup:upgrade
php bin/magento setup:di:compile
```

## License
Each source file included in this package is licensed under OSL 3.0.

[Open Software License (OSL 3.0)](https://opensource.org/licenses/osl-3.0.php).
Please see `LICENSE.txt` for full details of the OSL 3.0 license.

## Thanks for dropping by

<p align="center">
    <a href="https://magento.com">
        <img src="https://softcommerce.co.uk/pub/media/banner/logo.svg" width="200" alt="Soft Commerce Ltd" />
    </a>
    <br />
    <a href="https://softcommerce.co.uk/">
        https://softcommerce.co.uk/
    </a>
</p>




