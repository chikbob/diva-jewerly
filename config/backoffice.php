<?php

return [
    'role_aliases' => [
        'administrator' => 'admin',
        'super_admin' => 'admin',
        'super_user' => 'admin',
    ],

    'permissions' => [
        'admin' => [
            'panel.access',
            'admins.manage',
            'roles.manage',
            'catalog.view',
            'catalog.manage',
            'customers.view',
            'customers.manage',
            'operations.view',
        ],
        'catalog_manager' => [
            'panel.access',
            'catalog.view',
            'catalog.manage',
        ],
        'operations_manager' => [
            'panel.access',
            'customers.view',
            'operations.view',
        ],
        'support' => [
            'panel.access',
            'customers.view',
            'operations.view',
        ],
    ],
];
