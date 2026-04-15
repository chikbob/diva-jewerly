<?php

return [
    'metrics' => [
        'namespace' => env('METRICS_NAMESPACE', 'diva'),
        'token' => env('METRICS_TOKEN', ''),
    ],
    'alerts' => [
        'failed_jobs_threshold' => (int) env('FAILED_JOBS_ALERT_THRESHOLD', 5),
        'queue_backlog_threshold' => (int) env('QUEUE_BACKLOG_ALERT_THRESHOLD', 50),
    ],
];
