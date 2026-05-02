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
                    'title' => 'Dashboard Overview',
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
                    'title' => 'Employees',
                    'subtitle' => 'Team and payroll records',
                    'route' => 'employees.index',
                    'active' => ['employees.*'],
                    'icon' => [
                        'paths' => [
                            'M16 21v-2a4 4 0 00-4-4H6a4 4 0 00-4 4v2',
                            'M22 21v-2a4 4 0 00-3-3.87',
                            'M16 3.13a4 4 0 010 7.75',
                        ],
                        'circles' => [
                            ['cx' => '9', 'cy' => '7', 'r' => '4'],
                        ],
                    ],
                ],
                [
                    'title' => 'Packages',
                    'subtitle' => 'Plan templates and prices',
                    'route' => 'packages.index',
                    'active' => ['packages.*'],
                    'icon' => [
                        'paths' => [
                            'M20.59 13.41l-7.17 7.17a2 2 0 01-2.83 0L2 12V2h10l8.59 8.59a2 2 0 010 2.82z',
                            'M7 7h.01',
                        ],
                    ],
                ],
                [
                    'title' => 'Levels',
                    'subtitle' => 'Placement level list',
                    'route' => 'levels.index',
                    'active' => ['levels.*'],
                    'icon' => [
                        'paths' => [
                            'M4 20h16',
                            'M6 16h12',
                            'M8 12h8',
                            'M10 8h4',
                            'M12 4v4',
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
