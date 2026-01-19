<?php

namespace App\Services;

use Prometheus\CollectorRegistry;
use Prometheus\Counter;
use Prometheus\Gauge;
use Prometheus\Histogram;
use Prometheus\RenderTextFormat;
use Prometheus\Storage\Redis;

class MetricsService
{
    protected CollectorRegistry $registry;

    public function __construct()
    {
        //using redis for storage
        $redis = new Redis([
            'host' => env('REDIS_HOST', '127.0.0.1'),
            'port' => env('REDIS_PORT', 6379),
            'timeout' => 0.1
        ]);

        $this->registry = new CollectorRegistry($redis);
    }

    public function counter(string $name, string $help, array $labels = []): Counter
    {
        try {
            return $this->registry->getOrRegisterCounter(
                'url_shortener',
                $name,
                $help,
                $labels
            );
        } catch (\Exception $e) {
            return $this->registry->getCounter('url_shortener', $name);
        }
    }

    public function histogram(string $name, string $help, array $labels = []): Histogram
    {
        try {
            return $this->registry->getOrRegisterHistogram(
                'url_shortener',
                $name,
                $help,
                $labels
            );
        } catch (\Exception $e) {
            return $this->registry->getHistogram('url_shortener', $name);
        }
    }

    public function gauge(string $name, string $help, array $labels = []): Gauge
    {
        try {
            return $this->registry->getOrRegisterGauge(
                'url_shortener',
                $name,
                $help,
                $labels
            );
        } catch (\Exception $e) {
            return $this->registry->getGauge('url_shortener', $name);
        }
    }

    public function export(): string
    {
        $renderer = new RenderTextFormat();
        return $renderer->render($this->registry->getMetricFamilySamples());
    }
}
