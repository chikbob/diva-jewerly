<?php

namespace App\Http\Controllers;

use App\Support\MetricStore;
use App\Support\OperationalStatus;
use Illuminate\Http\Response;

class MetricsController extends Controller
{
    public function __construct(
        private readonly OperationalStatus $operationalStatus,
        private readonly MetricStore $metricStore
    )
    {
    }

    public function __invoke(): Response
    {
        $snapshot = $this->operationalStatus->snapshot();
        $namespace = config('operations.metrics.namespace', 'diva');

        $lines = [
            "# HELP {$namespace}_app_liveness_status Application liveness status.",
            "# TYPE {$namespace}_app_liveness_status gauge",
            "{$namespace}_app_liveness_status 1",
            "# HELP {$namespace}_app_readiness_status Application readiness status.",
            "# TYPE {$namespace}_app_readiness_status gauge",
            sprintf('%s_app_readiness_status %d', $namespace, $snapshot['healthy'] ? 1 : 0),
            "# HELP {$namespace}_readiness_check_status Readiness check status by check name.",
            "# TYPE {$namespace}_readiness_check_status gauge",
        ];

        foreach ($snapshot['checks'] as $name => $check) {
            $lines[] = sprintf(
                '%s_readiness_check_status{check="%s"} %d',
                $namespace,
                $name,
                $check['ok'] ? 1 : 0
            );
        }

        $lines[] = "# HELP {$namespace}_failed_jobs_total Number of failed jobs currently stored.";
        $lines[] = "# TYPE {$namespace}_failed_jobs_total gauge";
        $lines[] = sprintf('%s_failed_jobs_total %d', $namespace, (int) ($snapshot['failed_jobs']['count'] ?? 0));
        $lines[] = "# HELP {$namespace}_failed_jobs_threshold Alert threshold for failed jobs.";
        $lines[] = "# TYPE {$namespace}_failed_jobs_threshold gauge";
        $lines[] = sprintf('%s_failed_jobs_threshold %d', $namespace, (int) ($snapshot['failed_jobs']['threshold'] ?? 0));
        $lines[] = "# HELP {$namespace}_queue_backlog_jobs Queue backlog split by queue state.";
        $lines[] = "# TYPE {$namespace}_queue_backlog_jobs gauge";

        foreach (['ready_jobs' => 'ready', 'delayed_jobs' => 'delayed', 'reserved_jobs' => 'reserved'] as $field => $state) {
            $lines[] = sprintf(
                '%s_queue_backlog_jobs{queue="%s",driver="%s",state="%s"} %d',
                $namespace,
                $snapshot['queue']['name'] ?? 'default',
                $snapshot['queue']['driver'] ?? 'unknown',
                $state,
                (int) ($snapshot['queue'][$field] ?? 0)
            );
        }

        $lines[] = "# HELP {$namespace}_queue_backlog_total Total queue backlog currently observed.";
        $lines[] = "# TYPE {$namespace}_queue_backlog_total gauge";
        $lines[] = sprintf(
            '%s_queue_backlog_total{queue="%s",driver="%s"} %d',
            $namespace,
            $snapshot['queue']['name'] ?? 'default',
            $snapshot['queue']['driver'] ?? 'unknown',
            (int) ($snapshot['queue']['backlog'] ?? 0)
        );
        $lines[] = "# HELP {$namespace}_queue_backlog_threshold Alert threshold for queue backlog.";
        $lines[] = "# TYPE {$namespace}_queue_backlog_threshold gauge";
        $lines[] = sprintf(
            '%s_queue_backlog_threshold{queue="%s",driver="%s"} %d',
            $namespace,
            $snapshot['queue']['name'] ?? 'default',
            $snapshot['queue']['driver'] ?? 'unknown',
            (int) ($snapshot['queue']['threshold'] ?? 0)
        );
        $lines[] = "# HELP {$namespace}_operational_signal_status Alertable operational signal status.";
        $lines[] = "# TYPE {$namespace}_operational_signal_status gauge";

        foreach ($snapshot['signals'] as $signal) {
            $lines[] = sprintf('%s_operational_signal_status{signal="%s"} 1', $namespace, $signal);
        }

        if ($snapshot['signals'] === []) {
            $lines[] = sprintf('%s_operational_signal_status{signal="none"} 0', $namespace);
        }

        $lines = array_merge($lines, $this->renderRuntimeMetrics($namespace));
        $lines = array_merge($lines, $this->renderSloAndAlertMetadata($namespace));

        return response(implode("\n", $lines) . "\n", Response::HTTP_OK, [
            'Content-Type' => 'text/plain; version=0.0.4; charset=utf-8',
        ]);
    }

    private function renderRuntimeMetrics(string $namespace): array
    {
        $lines = [];

        foreach (collect($this->metricStore->snapshot())->groupBy('name') as $series) {
            $firstMetric = $series->first();
            $metricName = sprintf('%s_%s', $namespace, $firstMetric['name']);

            $lines[] = sprintf('# HELP %s %s', $metricName, $firstMetric['help']);
            $lines[] = sprintf('# TYPE %s %s', $metricName, $firstMetric['type']);

            foreach ($series as $metric) {
                if (($metric['type'] ?? null) === 'histogram') {
                    foreach ($metric['buckets'] as $bucket) {
                        $labels = array_merge($metric['labels'], ['le' => $this->formatNumeric($bucket['le'])]);
                        $lines[] = sprintf(
                            '%s%s %s',
                            $metricName . '_bucket',
                            $this->formatLabels($labels),
                            $this->formatNumeric($bucket['value'])
                        );
                    }

                    $lines[] = sprintf(
                        '%s%s %s',
                        $metricName . '_bucket',
                        $this->formatLabels(array_merge($metric['labels'], ['le' => '+Inf'])),
                        $this->formatNumeric($metric['count'])
                    );
                    $lines[] = sprintf(
                        '%s_sum%s %s',
                        $metricName,
                        $this->formatLabels($metric['labels']),
                        $this->formatNumeric($metric['sum'])
                    );
                    $lines[] = sprintf(
                        '%s_count%s %s',
                        $metricName,
                        $this->formatLabels($metric['labels']),
                        $this->formatNumeric($metric['count'])
                    );

                    continue;
                }

                $lines[] = sprintf(
                    '%s%s %s',
                    $metricName,
                    $this->formatLabels($metric['labels'] ?? []),
                    $this->formatNumeric($metric['value'] ?? 0)
                );
            }
        }

        return $lines;
    }

    private function renderSloAndAlertMetadata(string $namespace): array
    {
        $lines = [
            "# HELP {$namespace}_slo_target_ratio Declared SLO target ratios.",
            "# TYPE {$namespace}_slo_target_ratio gauge",
            sprintf(
                '%s_slo_target_ratio{service="platform",objective="availability"} %s',
                $namespace,
                $this->formatNumeric(((float) config('operations.slo.availability', 99.9)) / 100)
            ),
            sprintf(
                '%s_slo_target_ratio{service="checkout",objective="success_rate"} %s',
                $namespace,
                $this->formatNumeric(((float) config('operations.slo.checkout_success_rate', 99)) / 100)
            ),
            sprintf(
                '%s_slo_target_ratio{service="auth",objective="login_success_rate"} %s',
                $namespace,
                $this->formatNumeric(((float) config('operations.slo.login_success_rate', 95)) / 100)
            ),
            "# HELP {$namespace}_slo_target_seconds Declared SLO latency targets in seconds.",
            "# TYPE {$namespace}_slo_target_seconds gauge",
            sprintf(
                '%s_slo_target_seconds{service="http",objective="p95_latency"} %s',
                $namespace,
                $this->formatNumeric(config('operations.slo.http_p95_seconds', 0.5))
            ),
            sprintf(
                '%s_slo_target_seconds{service="queue",objective="p95_runtime"} %s',
                $namespace,
                $this->formatNumeric(config('operations.slo.queue_job_p95_seconds', 15))
            ),
            "# HELP {$namespace}_alert_threshold_ratio Configured alert thresholds expressed as ratios.",
            "# TYPE {$namespace}_alert_threshold_ratio gauge",
            sprintf(
                '%s_alert_threshold_ratio{signal="checkout_error_rate"} %s',
                $namespace,
                $this->formatNumeric(config('operations.alerts.checkout_error_rate_threshold', 0.1))
            ),
            sprintf(
                '%s_alert_threshold_ratio{signal="login_failure_rate"} %s',
                $namespace,
                $this->formatNumeric(config('operations.alerts.login_failure_rate_threshold', 0.25))
            ),
            "# HELP {$namespace}_alert_threshold_seconds Configured alert thresholds expressed as seconds.",
            "# TYPE {$namespace}_alert_threshold_seconds gauge",
            sprintf(
                '%s_alert_threshold_seconds{signal="http_request_p95"} %s',
                $namespace,
                $this->formatNumeric(config('operations.alerts.http_p95_seconds_threshold', 0.75))
            ),
            sprintf(
                '%s_alert_threshold_seconds{signal="queue_job_p95"} %s',
                $namespace,
                $this->formatNumeric(config('operations.alerts.queue_job_p95_seconds_threshold', 30))
            ),
            "# HELP {$namespace}_alert_route_info Configured alert routing labels.",
            "# TYPE {$namespace}_alert_route_info gauge",
            sprintf(
                '%s_alert_route_info{severity="default",receiver="%s"} 1',
                $namespace,
                config('operations.alerting.default_receiver', 'platform-default')
            ),
            sprintf(
                '%s_alert_route_info{severity="warning",receiver="%s"} 1',
                $namespace,
                config('operations.alerting.warning_receiver', 'platform-warning')
            ),
            sprintf(
                '%s_alert_route_info{severity="critical",receiver="%s"} 1',
                $namespace,
                config('operations.alerting.critical_receiver', 'platform-critical')
            ),
        ];

        return $lines;
    }

    private function formatLabels(array $labels): string
    {
        if ($labels === []) {
            return '';
        }

        $pairs = [];

        foreach ($labels as $key => $value) {
            $pairs[] = sprintf('%s="%s"', $key, addcslashes((string) $value, "\\\"\n"));
        }

        return sprintf('{%s}', implode(',', $pairs));
    }

    private function formatNumeric(int|float|string $value): string
    {
        if (is_int($value)) {
            return (string) $value;
        }

        $number = is_float($value) ? $value : (float) $value;

        return rtrim(rtrim(sprintf('%.6F', $number), '0'), '.');
    }
}
