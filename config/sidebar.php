<?php

return [
    'brand' => [
        'eyebrow' => 'Shukran',
        'title' => 'Admin Dashboard',
    ],

    'sections' => [
        [
            'title' => 'Main',
            'items' => [
                [
                    'title' => 'Dashboard',
                    'subtitle' => 'Overview and analytics',
                    'route' => 'dashboard',
                    'active' => ['dashboard'],
                    'icon' => [
                        'paths' => [
                            'M3 10.5L12 3l9 7.5V21a1 1 0 01-1 1h-5v-7H9v7H4a1 1 0 01-1-1v-10.5z',
                        ],
                    ],
                ],
                [
                    'title' => 'Customers',
                    'subtitle' => 'Manage your client base',
                    'route' => 'customers.index',
                    'active' => ['customers.*'],
                    'icon' => [
                        'paths' => [
                            'M17 21v-2a4 4 0 00-4-4H7a4 4 0 00-4 4v2',
                            'M23 21v-2a4 4 0 00-3-3.87M16 3.13a4 4 0 010 7.75',
                        ],
                        'circles' => [
                            ['cx' => '9', 'cy' => '7', 'r' => '4'],
                        ],
                    ],
                ],
                [
                    'title' => 'Profile',
                    'subtitle' => 'Account settings',
                    'route' => 'profile.edit',
                    'active' => ['profile.*'],
                    'icon' => [
                        'paths' => [
                            'M12 14c4.418 0 8 1.79 8 4v2H4v-2c0-2.21 3.582-4 8-4z',
                        ],
                        'circles' => [
                            ['cx' => '12', 'cy' => '7', 'r' => '4'],
                        ],
                    ],
                ],
            ],
        ],

    ],
];
