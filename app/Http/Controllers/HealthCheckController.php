<?php

namespace App\Http\Controllers;

use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Throwable;

class HealthCheckController extends Controller
{
    public function __invoke(): JsonResponse
    {
        $checks = [
            'database' => $this->checkDatabase(),
            'cache' => $this->checkCache(),
        ];

        $healthy = collect($checks)->every(fn (array $check): bool => $check['ok'] === true);

        return response()->json([
            'status' => $healthy ? 'ok' : 'degraded',
            'checks' => $checks,
            'timestamp' => now()->toIso8601String(),
            'request_id' => request()->attributes->get('request_id'),
        ], $healthy ? JsonResponse::HTTP_OK : JsonResponse::HTTP_SERVICE_UNAVAILABLE);
    }

    /**
     * @return array{ok: bool, error?: string}
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
            ];
        }
    }

    /**
     * @return array{ok: bool, error?: string}
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
                ];
            }

            Cache::forget($key);

            return ['ok' => true];
        } catch (Throwable $exception) {
            report($exception);

            return [
                'ok' => false,
                'error' => 'Cache interaction failed.',
            ];
        }
    }
}
