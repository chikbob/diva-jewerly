<?php

namespace App\Listeners;

use App\Support\MetricStore;
use Illuminate\Queue\Events\JobFailed;
use Illuminate\Queue\Events\JobProcessed;
use Illuminate\Queue\Events\JobProcessing;

class RecordQueueMetrics
{
    /**
     * @var array<string, float>
     */
    private array $startedAt = [];

    public function __construct(private readonly MetricStore $metricStore)
    {
    }

    public function whenProcessing(JobProcessing $event): void
    {
        $this->startedAt[$this->jobKey($event->job)] = microtime(true);
    }

    public function whenProcessed(JobProcessed $event): void
    {
        $this->recordCompletion($event->job, 'processed');
    }

    public function whenFailed(JobFailed $event): void
    {
        $this->recordCompletion($event->job, 'failed');
    }

    private function recordCompletion(object $job, string $outcome): void
    {
        $labels = [
            'job' => $this->jobName($job),
            'queue' => $this->jobQueue($job),
            'outcome' => $outcome,
        ];

        $this->metricStore->incrementCounter(
            'queue_jobs_total',
            'Queue jobs processed by queue, job name and outcome.',
            $labels
        );

        $startedAt = $this->startedAt[$this->jobKey($job)] ?? null;

        if ($startedAt !== null) {
            $this->metricStore->observeHistogram(
                'queue_job_duration_seconds',
                'Queue job processing duration in seconds by queue, job name and outcome.',
                microtime(true) - $startedAt,
                $labels,
                config('operations.metrics.queue.duration_buckets')
            );

            unset($this->startedAt[$this->jobKey($job)]);
        }
    }

    private function jobKey(object $job): string
    {
        if (method_exists($job, 'getJobId') && $job->getJobId() !== null) {
            return (string) $job->getJobId();
        }

        return spl_object_hash($job);
    }

    private function jobName(object $job): string
    {
        return method_exists($job, 'resolveName') ? (string) $job->resolveName() : class_basename($job);
    }

    private function jobQueue(object $job): string
    {
        return method_exists($job, 'getQueue') ? (string) ($job->getQueue() ?: 'default') : 'default';
    }
}
