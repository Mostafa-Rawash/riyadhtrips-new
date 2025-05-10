<?php

namespace Modules\Visa;

use Modules\Core\Abstracts\BaseSettingsClass;

class SettingClass extends BaseSettingsClass
{
    public static function getSettingPages()
    {
        return [
            [
                'id'   => 'visa',
                'title' => __('Visa Settings'),
                'position'=>20,
                'view'=>"Visa::admin.settings.visa",
                "keys"=>[
                    'visa_disable',
                    'visa_page_search_title',
                    'visa_page_search_banner',
                    'visa_layout_search',
                    'visa_location_search_style',
                    'visa_item_layout_style',
                    'visa_enable_review',
                    'visa_enable_gallery',
                    'visa_enable_extra_price',
                    'visa_enable_service_fee',
                    'visa_page_limit_item'
                ],
                'html_keys'=>[

                ]
            ]
        ];
    }
}
