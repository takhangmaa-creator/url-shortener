<?php

namespace App\Http\Controllers;

use App\Http\Requests\urlRequest;
use App\Services\MetricsService;
use App\Services\UrlService;
use Symfony\Component\HttpFoundation\Response;

class UrlController extends Controller
{
    protected $service;
    protected $metricsService;

    public function __construct(UrlService $service, MetricsService $metricsService)
    {
        $this->service = $service;
        $this->metricsService = $metricsService;
    }

    public function store(urlRequest $request)
    {
        $start = microtime(true);

        try {
            $validatedData = $request->validated();
            $url = $this->service->create($validatedData);

            $this->metricsService
                ->counter('url_operations_total', 'Total URL operations', ['operation', 'status'])
                ->inc(['operation' => 'create', 'status' => 'success']);

            $latency = microtime(true) - $start;
            $this->metricsService
                ->histogram('url_operation_latency_seconds', 'URL operation latency', ['operation'])
                ->observe($latency, ['operation' => 'create']);

            return response()->json(['short_url' => url("/api/url/{$url->short_code}")], Response::HTTP_CREATED);
        } catch (\Exception $e) {
            $this->metricsService
                ->counter('url_operations_total', 'Total URL operations', ['operation', 'status'])
                ->inc(['operation' => 'create', 'status' => 'error']);

            $latency = microtime(true) - $start;
            $this->metricsService
                ->histogram('url_operation_latency_seconds', 'URL operation latency', ['operation'])
                ->observe($latency, ['operation' => 'create']);

            throw $e;
        }
    }
}
