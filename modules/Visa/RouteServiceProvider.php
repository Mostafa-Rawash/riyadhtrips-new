<?php

namespace Modules\Visa;

use Illuminate\Support\Facades\Route;
use Illuminate\Foundation\Support\Providers\RouteServiceProvider as ServiceProvider;

class RouteServiceProvider extends ServiceProvider
{
    protected $moduleNamespace = 'Modules\Visa\Controllers';
    protected $adminModuleNamespace = 'Modules\Visa\Admin';

    public function boot()
    {
        parent::boot();
    }

    public function map()
    {
        $this->mapWebRoutes();
        $this->mapAdminRoutes();
    }

    protected function mapWebRoutes()
    {
        Route::middleware('web')
            ->namespace($this->moduleNamespace)
            ->group(__DIR__ . '/Routes/web.php');
    }

    protected function mapAdminRoutes()
    {
        Route::middleware(['web', 'admin'])
            ->namespace($this->adminModuleNamespace)
            ->group(__DIR__ . '/Routes/admin.php');
    }
}