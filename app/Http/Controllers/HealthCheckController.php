<?php

namespace App\Http\Controllers;

use App\Support\OperationalStatus;
use Illuminate\Http\JsonResponse;

class HealthCheckController extends Controller
{
    public function __construct(private readonly OperationalStatus $operationalStatus)
    {
    }

    public function __invoke(): JsonResponse
    {
        $snapshot = $this->operationalStatus->snapshot();

        return response()->json([
            'status' => $snapshot['healthy'] ? 'ok' : 'degraded',
            'checks' => $snapshot['checks'],
            'signals' => $snapshot['signals'],
            'timestamp' => now()->toIso8601String(),
            'request_id' => request()->attributes->get('request_id'),
        ], $snapshot['healthy'] ? JsonResponse::HTTP_OK : JsonResponse::HTTP_SERVICE_UNAVAILABLE);
    }
}
