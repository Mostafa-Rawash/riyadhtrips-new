<?php

namespace Modules\Visa;

use Modules\ModuleServiceProvider;
use Modules\Visa\Models\VisaApplication;
use Modules\User\Helpers\PermissionHelper;

class ModuleProvider extends ModuleServiceProvider
{
    public function boot()
    {
        $this->loadMigrationsFrom(__DIR__ . '/Migrations');

        // Register visa permissions
        PermissionHelper::add([
            // Visa
            'visa_view',
            'visa_manage',
            'visa_create',
            'visa_update',
            'visa_delete',
            'visa_statistics',
        ]);
    }

    public function register()
    {
        $this->app->register(RouterServiceProvider::class);
    }

    public static function getModuleInfo()
    {
        return [
            "name"        => __("Visa Management"),
            "category"    => __("Booking Services"),
            "description" => __("Manage visa applications and track visa status"),
            "version"     => "1.0",
            "author"      => "BookingCore",
            "model"       => VisaApplication::class,
            'display_order' => 0
        ];
    }

    public static function getAdminMenu()
    {
        return [
            'visa' => [
                'position' => 50,
                'url'      => route('visa.admin.index'),
                'title'    => __('Visa Management'),
                'icon'     => 'ion ion-ios-pricetag',
                'permission' => 'visa_view',
                'children' => [
                    'visa_index' => [
                        'url'        => route('visa.admin.index'),
                        'title'      => __('All Visa Applications'),
                        'permission' => 'visa_view',
                    ],
                    'visa_statistics' => [
                        'url'        => route('visa.admin.statistics'),
                        'title'      => __('Visa Statistics'),
                        'permission' => 'visa_statistics',
                    ],
                ]
            ]
        ];
    }

    public static function getUserMenu()
    {
        return [
            'visa' => [
                'url'      => route('visa.customer.history'),
                'title'    => __('Visa History'),
                'icon'     => 'icofont-compass',
                'position' => 40,
            ]
        ];
    }

    public static function getTemplateBlocks()
    {
        return [
            'visa_list'      => 'Visa::frontend.blocks.list',
            'visa_featured'  => 'Visa::frontend.blocks.featured',
        ];
    }
}
