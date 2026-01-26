<?php

namespace App\Http\Controllers;

use App\Models\Url;
use App\Services\MetricsService;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;

class RedirectController extends Controller
{
    protected MetricsService $metrics;

    public function __construct(MetricsService $metrics)
    {
        $this->metrics = $metrics;
    }

    public function __invoke(string $code)
    {
        $serverName = gethostname();
        // start timing for latency measurement
        $start = microtime(true);
        try {
            $key = "short_url:$code";
            $url = cache::get($key);

            if (! $url) {
                $url = Cache::lock("short_url_lock:$code", 10)->block(5, function () use ($code, $key) {
                    $url = Url::where('short_code', $code)
                        ->select('original_url', 'is_active', 'expires_at')
                        ->firstOrFail();
                    Cache::put($key, $url, now()->addHours(6));
                    return $url;
                });
            }

            if (! $url->is_active || ($url->expires_at && now()->gt($url->expires_at))) {
                $this->metrics
                    ->counter('redirects_total', 'Total redirect attempts', ['status'])
                    ->inc(['status' => 'expired']);

                abort(410, 'This short URL has expired or is no longer active');
            }

            $this->metrics
                ->counter('redirects_total', 'Total redirect attempts', ['status'])
                ->inc(['status' => 'success']);

            // latency of this redirect operation
            $latency = microtime(true) - $start;

            $this->metrics
                ->histogram('redirect_latency_seconds', 'Redirect latency in seconds')
                ->observe($latency);

            return redirect()->away($url->original_url, 301)->header('X-Served-By', $serverName);
        } catch (ModelNotFoundException $e) {
            $this->metrics
                ->counter('redirects_total', 'Total redirect attempts', ['status'])
                ->inc(['status' => 'not_found']);
            abort(404, 'Short URL not found');
        } catch (\Exception $e) {

            $this->metrics
                ->counter('redirects_total', 'Total redirect attempts', ['status'])
                ->inc(['status' => 'error']);

            $latency = microtime(true) - $start;
            $this->metrics
                ->histogram('redirect_latency_seconds', 'Redirect latency in seconds')
                ->observe($latency);

            throw $e;
        }
    }
}
