<?php

namespace Gabrielmoura\LaravelPennantRedis;

use Gabrielmoura\LaravelPennantRedis\Driver\RedisFeatureDriver;
use Illuminate\Contracts\Foundation\Application;
use Illuminate\Support\ServiceProvider;
use Laravel\Pennant\Feature;

class LaravelPennantRedisServiceProvider extends ServiceProvider
{
    public function boot(): void
    {
        Feature::extend('redis', function (Application $app) {
            return new RedisFeatureDriver($app->make('redis'), $app->make('events'), []);
        });
    }
}