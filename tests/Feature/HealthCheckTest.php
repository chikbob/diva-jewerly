<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class HealthCheckTest extends TestCase
{
    use RefreshDatabase;

    public function test_liveness_reports_ok_and_emits_request_id(): void
    {
        $response = $this->get(route('health.live'));

        $response
            ->assertOk()
            ->assertHeader('X-Request-Id')
            ->assertJsonPath('status', 'ok')
            ->assertJsonMissingPath('checks');
    }

    public function test_healthcheck_reports_ok_and_emits_request_id(): void
    {
        $response = $this->get(route('health.ready'));

        $response
            ->assertOk()
            ->assertHeader('X-Request-Id')
            ->assertJsonPath('status', 'ok')
            ->assertJsonPath('checks.database.ok', true)
            ->assertJsonPath('checks.cache.ok', true)
            ->assertJsonPath('checks.failed_jobs.ok', true)
            ->assertJsonPath('checks.queue_backlog.ok', true);
    }

    public function test_incoming_request_id_is_preserved(): void
    {
        $response = $this
            ->withHeader('X-Request-Id', 'health-check-request-123')
            ->get(route('health.ready'));

        $response
            ->assertOk()
            ->assertHeader('X-Request-Id', 'health-check-request-123')
            ->assertJsonPath('request_id', 'health-check-request-123');
    }

    public function test_up_route_remains_a_ready_alias(): void
    {
        $response = $this->get(route('health.up'));

        $response
            ->assertOk()
            ->assertJsonPath('checks.database.ok', true)
            ->assertJsonPath('checks.cache.ok', true)
            ->assertJsonPath('checks.failed_jobs.ok', true)
            ->assertJsonPath('checks.queue_backlog.ok', true);
    }
}
