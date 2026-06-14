<?php

namespace App\Http\Middleware;

use App\Support\MetricStore;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;
use Throwable;

class RecordHttpMetrics
{
    public function __construct(private readonly MetricStore $metricStore)
    {
    }

    public function handle(Request $request, Closure $next): Response
    {
        $startedAt = microtime(true);
        $statusCode = 500;

        try {
            $response = $next($request);
            $statusCode = $response->getStatusCode();

            return $response;
        } catch (Throwable $exception) {
            $statusCode = 500;

            throw $exception;
        } finally {
            $route = $request->route()?->getName() ?? trim($request->path(), '/');
            $route = $route === '' ? '/' : $route;

            if (! in_array($route, config('operations.metrics.http.excluded_routes', []), true)) {
                $labels = [
                    'method' => $request->method(),
                    'route' => $route,
                    'status_class' => $this->statusClass($statusCode),
                ];

                try {
                    $this->metricStore->incrementCounter(
                        'http_requests_total',
                        'Total HTTP requests observed by route, method and status class.',
                        $labels
                    );

                    $this->metricStore->observeHistogram(
                        'http_request_duration_seconds',
                        'HTTP request duration in seconds by route, method and status class.',
                        microtime(true) - $startedAt,
                        $labels,
                        config('operations.metrics.http.duration_buckets')
                    );
                } catch (Throwable $exception) {
                    report($exception);
                }
            }
        }
    }

    private function statusClass(int $statusCode): string
    {
        return sprintf('%dxx', (int) floor($statusCode / 100));
    }
}
