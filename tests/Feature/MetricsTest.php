<?php

namespace Tests\Feature;

use App\Listeners\RecordQueueMetrics;
use App\Models\CartItem;
use App\Models\Category;
use App\Models\Product;
use App\Models\User;
use App\Providers\RouteServiceProvider;
use App\Support\MetricStore;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Queue\Events\JobProcessed;
use Illuminate\Queue\Events\JobProcessing;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

class MetricsTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        app(MetricStore::class)->flush();
    }

    public function test_metrics_endpoint_exposes_prometheus_payload(): void
    {
        $this->get(route('home'))->assertOk();

        $response = $this->get(route('metrics.index'));

        $response
            ->assertOk()
            ->assertHeader('Content-Type', 'text/plain; version=0.0.4; charset=utf-8');

        $this->assertStringContainsString('diva_app_liveness_status 1', $response->getContent());
        $this->assertStringContainsString('diva_app_readiness_status 1', $response->getContent());
        $this->assertStringContainsString('diva_failed_jobs_total 0', $response->getContent());
        $this->assertStringContainsString('diva_http_requests_total{method="GET",route="home",status_class="2xx"} 1', $response->getContent());
        $this->assertStringContainsString('diva_slo_target_seconds{service="http",objective="p95_latency"} 0.5', $response->getContent());
        $this->assertStringContainsString('diva_alert_route_info{severity="critical",receiver="platform-critical"} 1', $response->getContent());
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

    public function test_auth_checkout_and_queue_metrics_are_exposed(): void
    {
        $user = User::factory()->create();

        $this->post('/login', [
            'email' => $user->email,
            'password' => 'wrong-password',
        ]);

        $this->post('/login', [
            'email' => $user->email,
            'password' => 'password',
        ])->assertRedirect(RouteServiceProvider::HOME);

        $category = Category::factory()->create();
        $product = Product::factory()->create([
            'category_id' => $category->id,
            'price' => 2500,
        ]);

        CartItem::query()->create([
            'user_id' => $user->id,
            'product_id' => $product->id,
            'quantity' => 1,
        ]);

        $this->actingAs($user)->post(route('checkout.store'), [
            'full_name' => 'Metrics Buyer',
            'email' => 'buyer@example.com',
            'payment_method' => 'cash_on_delivery',
        ])->assertRedirect('/');

        $job = new class
        {
            public function getJobId(): string
            {
                return 'job-1';
            }

            public function getQueue(): string
            {
                return 'default';
            }

            public function resolveName(): string
            {
                return 'App\\Jobs\\MetricsExampleJob';
            }
        };

        $queueMetrics = app(RecordQueueMetrics::class);
        $queueMetrics->whenProcessing(new JobProcessing('sync', $job));
        usleep(1000);
        $queueMetrics->whenProcessed(new JobProcessed('sync', $job));

        $metrics = $this->get(route('metrics.index'))->getContent();

        $this->assertStringContainsString('diva_auth_events_total{event="login_failed",guard="web"} 1', $metrics);
        $this->assertStringContainsString('diva_auth_events_total{event="login_succeeded",guard="web"} 1', $metrics);
        $this->assertStringContainsString('diva_checkout_orders_total{outcome="success",payment_method="cash_on_delivery"} 1', $metrics);
        $this->assertStringContainsString('diva_queue_jobs_total{job="App\\\\Jobs\\\\MetricsExampleJob",outcome="processed",queue="default"} 1', $metrics);
        $this->assertStringContainsString('diva_queue_job_duration_seconds_count{job="App\\\\Jobs\\\\MetricsExampleJob",outcome="processed",queue="default"} 1', $metrics);
    }
}
