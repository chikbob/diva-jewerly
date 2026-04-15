<?php

namespace App\Http\Controllers;

use Illuminate\Http\JsonResponse;

class LivenessCheckController extends Controller
{
    public function __invoke(): JsonResponse
    {
        return response()->json([
            'status' => 'ok',
            'service' => config('app.name'),
            'timestamp' => now()->toIso8601String(),
            'request_id' => request()->attributes->get('request_id'),
        ]);
    }
}
