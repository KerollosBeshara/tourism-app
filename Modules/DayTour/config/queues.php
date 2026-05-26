<?php

/**
 * DayTour Module Queue Configuration
 * 
 * Configure how image processing and other async tasks are handled
 */

return [
    'queues' => [
        // Image processing queue
        'images' => [
            'connection' => env('QUEUE_CONNECTION', 'redis'),
            'tries' => 3,
            'timeout' => 300,
            'backoff' => 120,
        ],
        // Cache invalidation queue (fast)
        'cache' => [
            'connection' => env('QUEUE_CONNECTION', 'redis'),
            'tries' => 2,
            'timeout' => 30,
            'backoff' => 5,
        ],
    ],

    // Job configuration
    'jobs' => [
        'upload_image' => [
            'timeout' => 300, // 5 minutes
            'tries' => 3,
            'retry_delay' => 120, // 2 minutes
        ],
        'process_image' => [
            'timeout' => 120, // 2 minutes
            'tries' => 3,
            'retry_delay' => 60, // 1 minute
        ],
        'delete_image' => [
            'timeout' => 60,
            'tries' => 3,
            'retry_delay' => 30,
        ],
    ],

    // Image processing settings
    'image_processing' => [
        'original_width' => 1200,
        'original_height' => 800,
        'quality' => 90,
        'thumbnail_width' => 300,
        'thumbnail_height' => 300,
        'medium_width' => 800,
        'medium_height' => 800,
        'format' => 'webp',
    ],
];
