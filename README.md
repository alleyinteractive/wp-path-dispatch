# WP Path Dispatch

[![Coding Standards](https://github.com/alleyinteractive/wp-path-dispatch/actions/workflows/coding-standards.yml/badge.svg)](https://github.com/alleyinteractive/wp-path-dispatch/actions/workflows/coding-standards.yml)
[![Testing Suite](https://github.com/alleyinteractive/wp-path-dispatch/actions/workflows/unit-test.yml/badge.svg)](https://github.com/alleyinteractive/wp-path-dispatch/actions/workflows/unit-test.yml)

Simply and easily add a URL which fires an action, triggers a callback, and/or loads a template.

## Installation

You can install the package via composer:

```bash
composer require alleyinteractive/wp-path-dispatch
```

## Usage

Use this package like so:

```php
$package = WP_Path_Dispatch\WP_Path_Dispatch\WP_Path_Dispatch();
$package->perform_magic();
```

## Changelog

Please see [CHANGELOG](CHANGELOG.md) for more information on what has changed recently.

## Credits

This project is actively maintained by [Alley
Interactive](https://github.com/alleyinteractive). Like what you see? [Come work
with us](https://alley.co/careers/).

- [Matt Boynes](https://github.com/Matt Boynes)
- [All Contributors](../../contributors)

## License

The GNU General Public License (GPL) license. Please see [License File](LICENSE) for more information.