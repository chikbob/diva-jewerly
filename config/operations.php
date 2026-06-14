<?php

return [
    'metrics' => [
        'namespace' => env('METRICS_NAMESPACE', 'diva'),
        'token' => env('METRICS_TOKEN', ''),
        'http' => [
            'excluded_routes' => [
                'health.live',
                'health.ready',
                'health.up',
                'metrics.index',
                'live',
                'ready',
                'up',
                'metrics',
            ],
            'duration_buckets' => [0.05, 0.1, 0.25, 0.5, 1, 2.5, 5],
        ],
        'checkout' => [
            'duration_buckets' => [0.1, 0.25, 0.5, 1, 2.5, 5, 10],
        ],
        'queue' => [
            'duration_buckets' => [0.05, 0.1, 0.25, 0.5, 1, 5, 15, 30, 60],
        ],
    ],
    'alerts' => [
        'failed_jobs_threshold' => (int) env('FAILED_JOBS_ALERT_THRESHOLD', 5),
        'queue_backlog_threshold' => (int) env('QUEUE_BACKLOG_ALERT_THRESHOLD', 50),
        'http_p95_seconds_threshold' => (float) env('HTTP_P95_SECONDS_ALERT_THRESHOLD', 0.75),
        'queue_job_p95_seconds_threshold' => (float) env('QUEUE_JOB_P95_SECONDS_ALERT_THRESHOLD', 30),
        'checkout_error_rate_threshold' => (float) env('CHECKOUT_ERROR_RATE_ALERT_THRESHOLD', 0.10),
        'login_failure_rate_threshold' => (float) env('LOGIN_FAILURE_RATE_ALERT_THRESHOLD', 0.25),
    ],
    'slo' => [
        'availability' => (float) env('SLO_AVAILABILITY_TARGET', 99.9),
        'http_p95_seconds' => (float) env('SLO_HTTP_P95_SECONDS', 0.5),
        'checkout_success_rate' => (float) env('SLO_CHECKOUT_SUCCESS_RATE', 99.0),
        'login_success_rate' => (float) env('SLO_LOGIN_SUCCESS_RATE', 95.0),
        'queue_job_p95_seconds' => (float) env('SLO_QUEUE_JOB_P95_SECONDS', 15.0),
    ],
    'alerting' => [
        'default_receiver' => env('ALERTMANAGER_DEFAULT_RECEIVER', 'platform-default'),
        'warning_receiver' => env('ALERTMANAGER_WARNING_RECEIVER', 'platform-warning'),
        'critical_receiver' => env('ALERTMANAGER_CRITICAL_RECEIVER', 'platform-critical'),
    ],
];
