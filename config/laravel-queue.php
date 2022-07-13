<?php

return [
    /*
    |--------------------------------------------------------------------------
    | Package enabled
    |--------------------------------------------------------------------------
    |
    | This option for enable/disable laravel queue.
    |
    */
    'enabled' => (bool) env('LARAVEL_QUEUE_ENABLED', true),

    /*
    |--------------------------------------------------------------------------
    | Default Filesystem Disk
    |--------------------------------------------------------------------------
    |
    | Here you may specify the default filesystem disk that should be used
    | by the framework. The "local" disk, as well as a variety of cloud
    | based disks are available to your application. Just store away!
    | if its null, it will use filesystems.default
    */
    'disk' => env('LARAVEL_QUEUE_DISK', 'local'),

    'single_process' => env('LARAVEL_QUEUE_SINGLE_PROC', true),

    'log_driver' => env('LARAVEL_QUEUE_LOG_CHANNEL', 'stack'),

    /*
     * How many job to execute per worker, int|null, null to execute all pending jobs
     */
    'jobs_execution_limit' => null,

    'human_readable_save' => false,

    'jobs_filename' => "jobs.json",

    'failed_jobs_filename' => "failed_jobs.json",

    'success_jobs_filename' => "success_jobs.json",

    'allow_add_executed_job' => false,

    'use_cache' => env('LARAVEL_QUEUE_CACHE', false),
];
