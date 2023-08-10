# Handling old zero-downtime deployments for you

[![Latest Version on Packagist](https://img.shields.io/packagist/v/emfits/laravel-cdcleaner.svg?style=flat-square)](https://packagist.org/packages/emfits/laravel-cdcleaner)
[![Total Downloads](https://img.shields.io/packagist/dt/emfits/laravel-cdcleaner.svg?style=flat-square)](https://packagist.org/packages/emfits/cdcleaner)
![GitHub Actions](https://github.com/emfITs/laravel-cdcleaner/actions/workflows/main.yml/badge.svg)

Handling old release directories with this package. Especially using zero-downtime deployments with one release directory and a linked current directory.

## Installation

You can install the package via composer:

```bash
composer require emfits/laravel-cdcleaner
```

## Commands

```bash
php artisan emfits:cdcleaner:clean
```

## Configuration
You can publish the config with:

```bash
php artisan vendor:publish --tag=cdcleaner-config
```

## Usage

```php
// Usage description here
```

### Testing

```bash
composer test
```

### Changelog

Please see [CHANGELOG](CHANGELOG.md) for more information what has changed recently.

## Contributing

Please see [CONTRIBUTING](CONTRIBUTING.md) for details.

### Security

If you discover any security related issues, please email m.nowocyn@emfits.de instead of using the issue tracker.

## Credits

-   [Marcel Nowocyn](https://github.com/emfits)
-   [All Contributors](../../contributors)

## License

The MIT License (MIT). Please see [License File](LICENSE.md) for more information.

## Laravel Package Boilerplate

This package was generated using the [Laravel Package Boilerplate](https://laravelpackageboilerplate.com).
