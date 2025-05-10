<?php

return [
    'visa_route_prefix' => 'visa',
    'visa_per_page' => 20,
    'visa_statuses' => [
        0 => 'Pending',
        1 => 'Processing',
        2 => 'Approved',
        3 => 'Rejected',
        4 => 'Cancelled',
        5 => 'Completed'
    ],
    'payment_statuses' => [
        'pending' => 'Pending',
        'paid' => 'Paid',
        'failed' => 'Failed'
    ],
    'contact_types' => [
        'mobile' => 'Mobile',
        'home' => 'Home',
        'work' => 'Work',
        'emergency' => 'Emergency'
    ]
];
