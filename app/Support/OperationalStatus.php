<?php

namespace App\Support;

use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Redis;
use Illuminate\Support\Facades\Schema;
use Throwable;

class OperationalStatus
{
    public function snapshot(): array
    {
        $database = $this->checkDatabase();
        $cache = $this->checkCache();
        $failedJobs = $this->checkFailedJobs();
        $queue = $this->checkQueueBacklog();

        $checks = [
            'database' => $database,
            'cache' => $cache,
            'failed_jobs' => $failedJobs,
            'queue_backlog' => $queue,
        ];

        $signals = collect($checks)
            ->filter(static fn (array $check): bool => ($check['ok'] ?? false) === false)
            ->map(static fn (array $check, string $name): string => (string) ($check['signal'] ?? "{$name}_degraded"))
            ->values()
            ->all();

        return [
            'healthy' => collect($checks)->every(static fn (array $check): bool => ($check['ok'] ?? false) === true),
            'checks' => $checks,
            'signals' => $signals,
            'failed_jobs' => [
                'count' => (int) ($failedJobs['count'] ?? 0),
                'threshold' => (int) ($failedJobs['threshold'] ?? 0),
            ],
            'queue' => [
                'driver' => (string) ($queue['driver'] ?? config('queue.default')),
                'name' => (string) ($queue['queue'] ?? 'default'),
                'ready_jobs' => (int) ($queue['ready_jobs'] ?? 0),
                'delayed_jobs' => (int) ($queue['delayed_jobs'] ?? 0),
                'reserved_jobs' => (int) ($queue['reserved_jobs'] ?? 0),
                'backlog' => (int) ($queue['backlog'] ?? 0),
                'threshold' => (int) ($queue['threshold'] ?? 0),
            ],
        ];
    }

    /**
     * @return array{ok: bool, error?: string, signal?: string}
     */
    private function checkDatabase(): array
    {
        try {
            DB::connection()->getPdo();

            return ['ok' => true];
        } catch (Throwable $exception) {
            report($exception);

            return [
                'ok' => false,
                'error' => 'Database connection failed.',
                'signal' => 'database_unavailable',
            ];
        }
    }

    /**
     * @return array{ok: bool, error?: string, signal?: string}
     */
    private function checkCache(): array
    {
        $key = 'healthcheck:ping';
        $value = (string) now()->getTimestamp();

        try {
            Cache::put($key, $value, now()->addMinute());

            if (Cache::get($key) !== $value) {
                return [
                    'ok' => false,
                    'error' => 'Cache round-trip failed.',
                    'signal' => 'cache_roundtrip_failed',
                ];
            }

            Cache::forget($key);

            return ['ok' => true];
        } catch (Throwable $exception) {
            report($exception);

            return [
                'ok' => false,
                'error' => 'Cache interaction failed.',
                'signal' => 'cache_unavailable',
            ];
        }
    }

    /**
     * @return array{ok: bool, count?: int, threshold?: int, error?: string, signal?: string}
     */
    private function checkFailedJobs(): array
    {
        $threshold = (int) config('operations.alerts.failed_jobs_threshold', 5);
        $table = (string) config('queue.failed.table', 'failed_jobs');

        try {
            if (! Schema::hasTable($table)) {
                return [
                    'ok' => true,
                    'count' => 0,
                    'threshold' => $threshold,
                ];
            }

            $count = DB::table($table)->count();

            return [
                'ok' => $count <= $threshold,
                'count' => $count,
                'threshold' => $threshold,
                'error' => $count > $threshold ? 'Failed jobs threshold exceeded.' : null,
                'signal' => 'failed_jobs_threshold_exceeded',
            ];
        } catch (Throwable $exception) {
            report($exception);

            return [
                'ok' => false,
                'count' => 0,
                'threshold' => $threshold,
                'error' => 'Failed jobs status could not be determined.',
                'signal' => 'failed_jobs_unavailable',
            ];
        }
    }

    /**
     * @return array{ok: bool, driver: string, queue: string, ready_jobs: int, delayed_jobs: int, reserved_jobs: int, backlog: int, threshold: int, error?: string, signal?: string}
     */
    private function checkQueueBacklog(): array
    {
        $driver = (string) config('queue.default', 'sync');
        $threshold = (int) config('operations.alerts.queue_backlog_threshold', 50);

        return match ($driver) {
            'redis' => $this->checkRedisQueueBacklog($threshold),
            'database' => $this->checkDatabaseQueueBacklog($threshold),
            default => [
                'ok' => true,
                'driver' => $driver,
                'queue' => 'default',
                'ready_jobs' => 0,
                'delayed_jobs' => 0,
                'reserved_jobs' => 0,
                'backlog' => 0,
                'threshold' => $threshold,
            ],
        };
    }

    /**
     * @return array{ok: bool, driver: string, queue: string, ready_jobs: int, delayed_jobs: int, reserved_jobs: int, backlog: int, threshold: int, error?: string, signal?: string}
     */
    private function checkRedisQueueBacklog(int $threshold): array
    {
        $queue = (string) config('queue.connections.redis.queue', 'default');
        $connection = (string) config('queue.connections.redis.connection', 'default');
        $prefix = (string) config('database.redis.options.prefix', '');
        $baseKey = "{$prefix}queues:{$queue}";

        try {
            $redis = Redis::connection($connection);
            $readyJobs = (int) $redis->llen($baseKey);
            $delayedJobs = (int) $redis->zcard("{$baseKey}:delayed");
            $reservedJobs = (int) $redis->zcard("{$baseKey}:reserved");
            $backlog = $readyJobs + $delayedJobs + $reservedJobs;

            return [
                'ok' => $backlog <= $threshold,
                'driver' => 'redis',
                'queue' => $queue,
                'ready_jobs' => $readyJobs,
                'delayed_jobs' => $delayedJobs,
                'reserved_jobs' => $reservedJobs,
                'backlog' => $backlog,
                'threshold' => $threshold,
                'error' => $backlog > $threshold ? 'Queue backlog threshold exceeded.' : null,
                'signal' => 'queue_backlog_threshold_exceeded',
            ];
        } catch (Throwable $exception) {
            report($exception);

            return [
                'ok' => false,
                'driver' => 'redis',
                'queue' => $queue,
                'ready_jobs' => 0,
                'delayed_jobs' => 0,
                'reserved_jobs' => 0,
                'backlog' => 0,
                'threshold' => $threshold,
                'error' => 'Queue backlog could not be determined.',
                'signal' => 'queue_backlog_unavailable',
            ];
        }
    }

    /**
     * @return array{ok: bool, driver: string, queue: string, ready_jobs: int, delayed_jobs: int, reserved_jobs: int, backlog: int, threshold: int, error?: string, signal?: string}
     */
    private function checkDatabaseQueueBacklog(int $threshold): array
    {
        $queue = (string) config('queue.connections.database.queue', 'default');
        $table = (string) config('queue.connections.database.table', 'jobs');

        try {
            if (! Schema::hasTable($table)) {
                return [
                    'ok' => true,
                    'driver' => 'database',
                    'queue' => $queue,
                    'ready_jobs' => 0,
                    'delayed_jobs' => 0,
                    'reserved_jobs' => 0,
                    'backlog' => 0,
                    'threshold' => $threshold,
                ];
            }

            $readyJobs = DB::table($table)->where('queue', $queue)->count();

            return [
                'ok' => $readyJobs <= $threshold,
                'driver' => 'database',
                'queue' => $queue,
                'ready_jobs' => $readyJobs,
                'delayed_jobs' => 0,
                'reserved_jobs' => 0,
                'backlog' => $readyJobs,
                'threshold' => $threshold,
                'error' => $readyJobs > $threshold ? 'Queue backlog threshold exceeded.' : null,
                'signal' => 'queue_backlog_threshold_exceeded',
            ];
        } catch (Throwable $exception) {
            report($exception);

            return [
                'ok' => false,
                'driver' => 'database',
                'queue' => $queue,
                'ready_jobs' => 0,
                'delayed_jobs' => 0,
                'reserved_jobs' => 0,
                'backlog' => 0,
                'threshold' => $threshold,
                'error' => 'Queue backlog could not be determined.',
                'signal' => 'queue_backlog_unavailable',
            ];
        }
    }
}
