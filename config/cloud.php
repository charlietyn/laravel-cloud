<?php

declare(strict_types=1);

return [
    'region' => env('AWS_DEFAULT_REGION', 'eu-west-2'),
    'endpoint' => env('AWS_ENDPOINT_URL'),

    // Leave both null in AWS production to use the standard AWS credential chain.
    'credentials' => [
        'key' => env('AWS_ACCESS_KEY_ID'),
        'secret' => env('AWS_SECRET_ACCESS_KEY'),
        'token' => env('AWS_SESSION_TOKEN'),
    ],

    'http' => [
        'connect_timeout_seconds' => (float) env('AWS_CONNECT_TIMEOUT_SECONDS', 2.0),
        'timeout_seconds' => (float) env('AWS_TIMEOUT_SECONDS', 10.0),
        'retries' => (int) env('AWS_MAX_RETRIES', 3),
    ],

    's3' => [
        'bucket' => env('AWS_BUCKET', 'myapp-local'),
        'path_style' => (bool) env('AWS_USE_PATH_STYLE_ENDPOINT', false),
    ],

    // Application code uses logical aliases; adapters resolve them here.
    'queues' => [
        'default' => env('AWS_SQS_DEFAULT_QUEUE', 'myapp-default'),
    ],

    'topics' => [
        // Value may be an ARN or an existing topic name. cloud:bootstrap can create the name.
        'default' => env('AWS_SNS_DEFAULT_TOPIC', 'myapp-events'),
    ],

    'event_buses' => [
        'default' => env('AWS_EVENT_BUS', 'default'),
    ],

    'secrets' => [
        'default' => env('AWS_SECRET_DEFAULT', 'myapp/default'),
    ],

    'documents' => [
        'collections' => [
            'default' => [
                'table' => env('AWS_DYNAMODB_DEFAULT_TABLE', 'myapp-documents'),
                'id_attribute' => 'id',
                'payload_attribute' => 'payload',
            ],
        ],
    ],

    'functions' => [
        'default' => env('AWS_LAMBDA_DEFAULT_FUNCTION', 'myapp-default'),
    ],

    // Used only by cloud:bootstrap in local/testing environments.
    'bootstrap' => [
        'buckets' => [env('AWS_BUCKET', 'myapp-local')],
        'queues' => [env('AWS_SQS_DEFAULT_QUEUE', 'myapp-default')],
        'topics' => [env('AWS_SNS_DEFAULT_TOPIC', 'myapp-events')],
        'event_buses' => array_values(array_filter([
            env('AWS_EVENT_BUS', 'default') !== 'default' ? env('AWS_EVENT_BUS') : null,
        ])),
        'document_tables' => [
            [
                'table' => env('AWS_DYNAMODB_DEFAULT_TABLE', 'myapp-documents'),
                'id_attribute' => 'id',
            ],
        ],
    ],
];
