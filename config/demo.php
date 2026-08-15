<?php

return [
    /*
    |--------------------------------------------------------------------------
    | Public portfolio demo mode
    |--------------------------------------------------------------------------
    |
    | DEMO_MODE is intended for an Internet-facing recruiter demo. It keeps the
    | operational workflow usable while protecting configuration/master data
    | from anonymous demo users who know the published credentials.
    |
    */

    'enabled' => (bool) env('DEMO_MODE', false),

    'password' => env('DEMO_PASSWORD', 'password'),
    'default_email' => env('DEMO_DEFAULT_EMAIL', 'employee@example.com'),

    'uploads_enabled' => (bool) env('DEMO_UPLOADS_ENABLED', false),
    'upload_max_kb' => (int) env('DEMO_UPLOAD_MAX_KB', 512),
    'normal_upload_max_kb' => (int) env('WORKFLOW_UPLOAD_MAX_KB', 5120),

    'max_writes_per_minute' => (int) env('DEMO_MAX_WRITES_PER_MINUTE', 15),
    'max_writes_per_hour' => (int) env('DEMO_MAX_WRITES_PER_HOUR', 60),

    // Read operations remain available so recruiters can inspect configuration.
    // Mutating requests and create/edit form pages matching these patterns are
    // blocked while DEMO_MODE=true. Operational PR/approval/PO/GR/asset flows
    // deliberately remain writable.
    'read_only_routes' => [
        'admin.*',
        'inventory.item-categories.*',
        'inventory.items.*',
        'inventory.warehouses.*',
        'inventory.receipts.*',
        'procurement.suppliers.*',
        'assets.edit',
        'assets.update',
    ],

    'accounts' => [
        ['email' => 'employee@example.com', 'role' => 'Nhân viên'],
        ['email' => 'manager@example.com', 'role' => 'Quản lý'],
        ['email' => 'procurement@example.com', 'role' => 'Mua sắm'],
        ['email' => 'finance@example.com', 'role' => 'Tài chính'],
        ['email' => 'director@example.com', 'role' => 'Giám đốc'],
        ['email' => 'asset@example.com', 'role' => 'Quản lý tài sản'],
        ['email' => 'hr@example.com', 'role' => 'Nhân sự'],
        ['email' => 'admin@example.com', 'role' => 'Quản trị viên · chỉ đọc cấu hình'],
    ],
];
