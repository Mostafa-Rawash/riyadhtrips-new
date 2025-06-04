<?php

namespace Modules\Visa;

use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\Route;

class ModuleServiceProvider extends ServiceProvider
{
    public function boot()
    {
        $this->loadViewsFrom(__DIR__ . '/Views', 'Visa');
        
        $this->loadMigrationsFrom(__DIR__ . '/Database/Migrations');
        
        if (is_installed()) {
            $this->loadRoutesFrom(__DIR__ . '/Routes/web.php');
            $this->loadRoutesFrom(__DIR__ . '/Routes/admin.php');
        }
    }
    
    public function register()
    {
        $this->app->register(RouteServiceProvider::class);
    }
    
    public static function getAdminMenu()
    {
        return [
            'visa' => [
                'position' => 40,
                'title' => __('Visa Applications'),
                'icon' => 'fa fa-passport',
                'permission' => 'visa_view',
                'children' => [
                    'all' => [
                        'position' => 10,
                        'title' => __('All Applications'),
                        'url' => route('visa.admin.index'),
                        'permission' => 'visa_view',
                    ],
                ]
            ]
        ];
    }
}