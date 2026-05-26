<?php

return [
    'name' => 'DayTour',

    // S3 Configuration
    's3' => [
        'disk' => env('FILESYSTEM_DISK', 's3'),
        'bucket' => env('AWS_BUCKET'),
        'region' => env('AWS_DEFAULT_REGION', 'us-east-1'),
        'image_path' => 'day-tours',
        'max_file_size' => 10240, // 10MB in KB
    ],

    // Cache Configuration
    'cache' => [
        'enabled' => env('DAYTOUR_CACHE_ENABLED', true),
        'ttl' => env('DAYTOUR_CACHE_TTL', 3600), // 1 hour in seconds
        'prefix' => 'day_tour',
    ],

    // Query Performance
    'pagination' => [
        'per_page' => 15,
        'max_per_page' => 100,
    ],

    // Image Optimization
    'images' => [
        'thumbnail_width' => 300,
        'thumbnail_height' => 300,
        'allowed_formats' => ['jpeg', 'png', 'jpg', 'gif', 'webp'],
    ],
];

