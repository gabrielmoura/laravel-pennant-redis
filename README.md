<p align="center">
<img src="art/art.webp">
</p>

<p align="center">
<a href="https://packagist.org/packages/gabrielmoura/laravel-pennant-redis"><img src="https://img.shields.io/packagist/v/gabrielmoura/laravel-pennant-redis" alt="Latest Stable Version"></a>
<a href="https://packagist.org/packages/gabrielmoura/laravel-pennant-redis"><img src="https://img.shields.io/packagist/l/gabrielmoura/laravel-pennant-redis" alt="License"></a>
</p>

## Introduction

A Redis Driver for the [Laravel Pennant](https://github.com/laravel/pennant).
Now compatible with Laravel 11 and igbinary.

## Compatibility
- Laravel 10
- Laravel 11
- IgBinary

## Objective

Considering the necessity of employing storage other than arrays or databases, I took the liberty of crafting a driver
to provide native support for Redis.

The advantage lies in not being confined to a single Laravel instance or the database, which already contends with its
challenges of overload.

This minor alteration is a replica of the database driver, with adaptations for writing and reading Hash in Redis.

## Installation

```bash
composer require gabrielmoura/laravel-pennant-redis
```

```php
/* config/pennant.php */

   'stores' => [
      'redis' => [
            'driver' => 'redis',
            'connection' => 'default',
        ],

    ],
```
