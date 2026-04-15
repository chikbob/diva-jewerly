<?php

namespace App\Http\Controllers;

use App\Support\OperationalStatus;
use Illuminate\Http\Response;

class MetricsController extends Controller
{
    public function __construct(private readonly OperationalStatus $operationalStatus)
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

        return response(implode("\n", $lines) . "\n", Response::HTTP_OK, [
            'Content-Type' => 'text/plain; version=0.0.4; charset=utf-8',
        ]);
    }
}
