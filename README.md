## Introduction
A Redis Driver for the [Laravel Pennant](https://github.com/laravel/pennant).

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
