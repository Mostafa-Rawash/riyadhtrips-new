<?php
namespace Themes\BC\Visa;

use Modules\ModuleServiceProvider;

class ModuleProvider extends ModuleServiceProvider
{
    public function boot()
    {
        // Load the theme-specific views
        $this->loadViewsFrom(__DIR__ . '/Views', 'Visa');
    }

    public static function getAdminMenu()
    {
        return [
            "position" => 50,
            'url'      => route('visa.admin.index'),
            'title'    => __('Visa Management'),
            'icon'     => 'ion ion-ios-pricetag',
            'permission' => 'visa_manage',
            'children' => [
                [
                    'url'        => route('visa.admin.index'),
                    'title'      => __('All Visa Applications'),
                    'permission' => 'visa_manage',
                ],
                [
                    'url'        => route('visa.admin.statistics'),
                    'title'      => __('Visa Statistics'),
                    'permission' => 'visa_manage',
                ],
            ]
        ];
    }

    public static function getUserMenu()
    {
        return [
            [
                'url'    => route('visa.customer.history'),
                'title'  => __('Visa History'),
                'icon'   => 'icon-pricetag',
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
