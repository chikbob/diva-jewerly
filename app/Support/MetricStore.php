<?php

namespace App\Support;

use Illuminate\Support\Facades\Cache;

class MetricStore
{
    private const INDEX_KEY = 'metrics:index';

    public function incrementCounter(string $name, string $help, array $labels = [], int|float $value = 1): void
    {
        $cacheKey = $this->seriesKey('counter', $name, $labels);

        Cache::add($cacheKey, 0);
        Cache::increment($cacheKey, $value);

        $this->register([
            'type' => 'counter',
            'name' => $name,
            'help' => $help,
            'labels' => $this->normalizeLabels($labels),
            'key' => $cacheKey,
        ]);
    }

    public function setGauge(string $name, string $help, int|float $value, array $labels = []): void
    {
        $cacheKey = $this->seriesKey('gauge', $name, $labels);

        Cache::forever($cacheKey, $value);

        $this->register([
            'type' => 'gauge',
            'name' => $name,
            'help' => $help,
            'labels' => $this->normalizeLabels($labels),
            'key' => $cacheKey,
        ]);
    }

    public function observeHistogram(
        string $name,
        string $help,
        int|float $value,
        array $labels = [],
        ?array $buckets = null
    ): void {
        $normalizedLabels = $this->normalizeLabels($labels);
        $normalizedBuckets = $this->normalizeBuckets($buckets);
        $seriesKey = $this->seriesKey('histogram', $name, $normalizedLabels);

        foreach ($normalizedBuckets as $bucket) {
            $bucketKey = sprintf('%s:bucket:%s', $seriesKey, $this->bucketSuffix($bucket));

            Cache::add($bucketKey, 0);

            if ($value <= $bucket) {
                Cache::increment($bucketKey);
            }
        }

        $countKey = sprintf('%s:count', $seriesKey);
        $sumKey = sprintf('%s:sum', $seriesKey);

        Cache::add($countKey, 0);
        Cache::increment($countKey);

        Cache::forever($sumKey, ((float) Cache::get($sumKey, 0)) + (float) $value);

        $this->register([
            'type' => 'histogram',
            'name' => $name,
            'help' => $help,
            'labels' => $normalizedLabels,
            'key' => $seriesKey,
            'buckets' => $normalizedBuckets,
        ]);
    }

    public function snapshot(): array
    {
        return collect(Cache::get(self::INDEX_KEY, []))
            ->map(function (array $descriptor): array {
                if (($descriptor['type'] ?? null) === 'histogram') {
                    return $this->hydrateHistogram($descriptor);
                }

                $descriptor['value'] = $this->numeric(Cache::get($descriptor['key'] ?? '', 0));

                return $descriptor;
            })
            ->sortBy(fn (array $descriptor): string => $descriptor['name'] . json_encode($descriptor['labels'] ?? []))
            ->values()
            ->all();
    }

    public function flush(): void
    {
        $descriptors = Cache::get(self::INDEX_KEY, []);

        foreach ($descriptors as $descriptor) {
            if (($descriptor['type'] ?? null) === 'histogram') {
                foreach (($descriptor['buckets'] ?? []) as $bucket) {
                    Cache::forget(sprintf('%s:bucket:%s', $descriptor['key'], $this->bucketSuffix((float) $bucket)));
                }

                Cache::forget(sprintf('%s:count', $descriptor['key']));
                Cache::forget(sprintf('%s:sum', $descriptor['key']));

                continue;
            }

            Cache::forget($descriptor['key'] ?? '');
        }

        Cache::forget(self::INDEX_KEY);
    }

    private function hydrateHistogram(array $descriptor): array
    {
        $buckets = [];

        foreach (($descriptor['buckets'] ?? []) as $bucket) {
            $buckets[] = [
                'le' => (float) $bucket,
                'value' => $this->numeric(Cache::get(
                    sprintf('%s:bucket:%s', $descriptor['key'], $this->bucketSuffix((float) $bucket)),
                    0
                )),
            ];
        }

        $descriptor['buckets'] = $buckets;
        $descriptor['count'] = $this->numeric(Cache::get(sprintf('%s:count', $descriptor['key']), 0));
        $descriptor['sum'] = $this->numeric(Cache::get(sprintf('%s:sum', $descriptor['key']), 0));

        return $descriptor;
    }

    private function register(array $descriptor): void
    {
        $descriptors = collect(Cache::get(self::INDEX_KEY, []))
            ->reject(fn (array $existing): bool => ($existing['key'] ?? null) === ($descriptor['key'] ?? null))
            ->push($descriptor)
            ->values()
            ->all();

        Cache::forever(self::INDEX_KEY, $descriptors);
    }

    private function seriesKey(string $type, string $name, array $labels): string
    {
        return sprintf('metrics:%s:%s:%s', $type, $name, sha1(json_encode($labels)));
    }

    private function normalizeLabels(array $labels): array
    {
        ksort($labels);

        return collect($labels)
            ->mapWithKeys(static fn (mixed $value, string $key): array => [(string) $key => (string) $value])
            ->all();
    }

    private function normalizeBuckets(?array $buckets): array
    {
        $normalized = collect($buckets ?? [0.05, 0.1, 0.25, 0.5, 1, 2.5, 5, 10])
            ->map(static fn (mixed $value): float => (float) $value)
            ->filter(static fn (float $value): bool => $value > 0)
            ->unique()
            ->sort()
            ->values()
            ->all();

        return $normalized === [] ? [1.0] : $normalized;
    }

    private function bucketSuffix(float $bucket): string
    {
        return str_replace('.', '_', rtrim(rtrim(sprintf('%.5F', $bucket), '0'), '.'));
    }

    private function numeric(mixed $value): int|float
    {
        if (is_int($value) || is_float($value)) {
            return $value;
        }

        return str_contains((string) $value, '.') ? (float) $value : (int) $value;
    }
}
