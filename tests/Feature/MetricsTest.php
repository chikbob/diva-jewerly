<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

class MetricsTest extends TestCase
{
    use RefreshDatabase;

    public function test_metrics_endpoint_exposes_prometheus_payload(): void
    {
        $response = $this->get(route('metrics.index'));

        $response
            ->assertOk()
            ->assertHeader('Content-Type', 'text/plain; version=0.0.4; charset=utf-8');

        $this->assertStringContainsString('diva_app_liveness_status 1', $response->getContent());
        $this->assertStringContainsString('diva_app_readiness_status 1', $response->getContent());
        $this->assertStringContainsString('diva_failed_jobs_total 0', $response->getContent());
    }

    public function test_metrics_endpoint_requires_token_when_configured(): void
    {
        config()->set('operations.metrics.token', 'metrics-secret');

        $this->get(route('metrics.index'))->assertForbidden();

        $this->withHeader('Authorization', 'Bearer metrics-secret')
            ->get(route('metrics.index'))
            ->assertOk();
    }

    public function test_readiness_degrades_when_failed_jobs_threshold_is_exceeded(): void
    {
        config()->set('operations.alerts.failed_jobs_threshold', 0);

        DB::table('failed_jobs')->insert([
            'uuid' => '11111111-1111-1111-1111-111111111111',
            'connection' => 'redis',
            'queue' => 'default',
            'payload' => '{}',
            'exception' => 'boom',
            'failed_at' => now(),
        ]);

        $response = $this->get(route('health.ready'));

        $response
            ->assertStatus(503)
            ->assertJsonPath('checks.failed_jobs.ok', false)
            ->assertJsonPath('checks.failed_jobs.count', 1)
            ->assertJsonPath('signals.0', 'failed_jobs_threshold_exceeded');
    }

    public function test_readiness_degrades_when_database_queue_backlog_threshold_is_exceeded(): void
    {
        config()->set('queue.default', 'database');
        config()->set('operations.alerts.queue_backlog_threshold', 0);

        DB::table('jobs')->insert([
            'queue' => 'default',
            'payload' => '{}',
            'attempts' => 0,
            'reserved_at' => null,
            'available_at' => now()->timestamp,
            'created_at' => now()->timestamp,
        ]);

        $response = $this->get(route('health.ready'));

        $response
            ->assertStatus(503)
            ->assertJsonPath('checks.queue_backlog.ok', false)
            ->assertJsonPath('checks.queue_backlog.backlog', 1)
            ->assertJsonPath('signals.0', 'queue_backlog_threshold_exceeded');
    }
}
