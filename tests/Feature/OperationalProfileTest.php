<?php

namespace Tests\Feature;

use Tests\TestCase;

class OperationalProfileTest extends TestCase
{
    public function test_docker_environment_uses_redis_backed_runtime_defaults(): void
    {
        $env = file_get_contents(base_path('.env.docker.example'));

        $this->assertIsString($env);
        $this->assertStringContainsString('CACHE_DRIVER=redis', $env);
        $this->assertStringContainsString('QUEUE_CONNECTION=redis', $env);
        $this->assertStringContainsString('REDIS_HOST=redis', $env);
        $this->assertStringContainsString('QUEUE_RETRY_AFTER=120', $env);
    }

    public function test_operational_scripts_exist_and_are_executable(): void
    {
        $scripts = [
            'scripts/smoke-check.sh',
            'scripts/metrics-check.sh',
            'scripts/post-deploy.sh',
            'scripts/render-monitoring-config.sh',
            'scripts/backup-database.sh',
            'scripts/backup-storage.sh',
            'scripts/restore-database.sh',
            'scripts/restore-storage.sh',
        ];

        foreach ($scripts as $script) {
            $path = base_path($script);

            $this->assertFileExists($path);
            $this->assertTrue(is_executable($path), sprintf('%s should be executable.', $script));
        }
    }
}
