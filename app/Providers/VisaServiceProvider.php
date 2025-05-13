<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\Config;

class VisaServiceProvider extends ServiceProvider
{
    /**
     * Register services.
     *
     * @return void
     */
    public function register()
    {
        // Register external database configuration
        $this->app->booted(function () {
            Config::set('database.connections.visa_external', [
                'driver' => 'mysql',
                'host' => env('DB_EXTERNAL_HOST', '127.0.0.1'),
                'port' => env('DB_EXTERNAL_PORT', '3306'),
                'database' => env('DB_EXTERNAL_DATABASE', 'riyaoeiu_subvisadom'),
                'username' => env('DB_EXTERNAL_USERNAME', 'riyaoeiu_subvisadom'),
                'password' => env('DB_EXTERNAL_PASSWORD', 'nQ~YWEKH6g*3'),
                'unix_socket' => env('DB_EXTERNAL_SOCKET', ''),
                'charset' => 'utf8mb4',
                'collation' => 'utf8mb4_unicode_ci',
                'prefix' => '',
                'prefix_indexes' => true,
                'strict' => false,
                'engine' => 'InnoDB',
                'options' => extension_loaded('pdo_mysql') ? array_filter([
                    \PDO::MYSQL_ATTR_SSL_CA => env('MYSQL_ATTR_SSL_CA'),
                ]) : [],
            ]);
        });
    }

    /**
     * Bootstrap services.
     *
     * @return void
     */
    public function boot()
    {
        //
    }
}
