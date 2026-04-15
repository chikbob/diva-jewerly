<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class HealthCheckTest extends TestCase
{
    use RefreshDatabase;

    public function test_healthcheck_reports_ok_and_emits_request_id(): void
    {
        $response = $this->get(route('health.up'));

        $response
            ->assertOk()
            ->assertHeader('X-Request-Id')
            ->assertJsonPath('status', 'ok')
            ->assertJsonPath('checks.database.ok', true)
            ->assertJsonPath('checks.cache.ok', true);
    }

    public function test_incoming_request_id_is_preserved(): void
    {
        $response = $this
            ->withHeader('X-Request-Id', 'health-check-request-123')
            ->get(route('health.up'));

        $response
            ->assertOk()
            ->assertHeader('X-Request-Id', 'health-check-request-123')
            ->assertJsonPath('request_id', 'health-check-request-123');
    }
}
