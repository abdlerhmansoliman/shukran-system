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
                    'permission' => 'view customers',
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
                    'title' => 'Products',
                    'subtitle' => 'Class products and enrollments',
                    'route' => 'groups.index',
                    'active' => ['groups.*'],
                    'permission' => 'view groups',
                    'icon' => [
                        'paths' => [
                            'M17 20h5v-2a4 4 0 00-4-4h-1',
                            'M9 20H4v-2a4 4 0 014-4h1',
                            'M12 12a4 4 0 100-8 4 4 0 000 8z',
                            'M6 8a3 3 0 100 6',
                            'M18 8a3 3 0 110 6',
                        ],
                    ],
                ],
                [
                    'title' => 'Employees',
                    'subtitle' => 'Team and payroll records',
                    'route' => 'employees.index',
                    'active' => ['employees.*'],
                    'permission' => 'view employees',
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
                    'title' => 'Customization',
                    'subtitle' => 'System settings',
                    'icon' => [
                        'paths' => [
                            'M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z',
                            'M15 12a3 3 0 11-6 0 3 3 0 016 0z',
                        ],
                    ],
                    'items' => [
                        [
                            'title' => 'Packages',
                            'route' => 'packages.index',
                            'active' => ['packages.*'],
                            'permission' => 'view packages',
                        ],
                        [
                            'title' => 'Discounts',
                            'route' => 'discounts.index',
                            'active' => ['discounts.*'],
                            'permission' => 'view discounts',
                        ],
                        [
                            'title' => 'Levels',
                            'route' => 'levels.index',
                            'active' => ['levels.*'],
                            'permission' => 'view levels',
                        ],
                        [
                            'title' => 'Categories',
                            'route' => 'categories.index',
                            'active' => ['categories.*'],
                            'permission' => 'view categories',
                        ],
                        [
                            'title' => 'Roles & Permissions',
                            'route' => 'roles.index',
                            'active' => ['roles.*'],
                            'permission' => 'view roles',
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
