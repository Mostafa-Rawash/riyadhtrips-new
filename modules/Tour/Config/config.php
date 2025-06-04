<?php
// File: modules/Tour/Config/config.php (Enhanced)

return [
    'tour_route_prefix' => env('TOUR_ROUTE_PREFIX', 'tours'),
    
    // Time slots configuration
    'time_slots' => [
        'enabled' => true,
        'cache_duration' => 300, // 5 minutes
        'real_time_updates_interval' => 30, // seconds
        'max_booking_cutoff_hours' => 72,
        'default_booking_cutoff_hours' => 2,
        'max_slots_per_day' => 20,
        'allow_price_modifiers' => true,
        'show_remaining_capacity' => true,
        'highlight_popular_slots' => true,
        'auto_disable_past_slots' => true
    ],
    
    // Booking configuration
    'booking' => [
        'enable_guest_checkout' => false,
        'require_phone_number' => true,
        'max_guests_per_booking' => 50,
        'allow_same_day_booking' => true,
        'booking_confirmation_required' => true
    ],
    
    // Pricing configuration
    'pricing' => [
        'enable_dynamic_pricing' => false,
        'enable_seasonal_pricing' => true,
        'enable_group_discounts' => true,
        'enable_early_bird_discounts' => false
    ],
    
    // Cache configuration
    'cache' => [
        'availability_ttl' => 300,
        'search_results_ttl' => 600,
        'tour_details_ttl' => 1800
    ],
    
    // Features
    'features' => [
        'enable_reviews' => true,
        'enable_wishlist' => true,
        'enable_comparison' => true,
        'enable_map_integration' => true,
        'enable_weather_info' => false,
        'enable_social_sharing' => true
    ],
    
    // SEO configuration
    'seo' => [
        'enable_structured_data' => true,
        'enable_meta_generation' => true,
        'enable_sitemap' => true
    ]
];