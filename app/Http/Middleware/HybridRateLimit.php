<?php

namespace App\Http\Middleware;

use App\RateLimiting\SlidingWindowLimiter;
use App\RateLimiting\TokenBucketLimiter;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class HybridRateLimit
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(
        Request $request,
        Closure $next,
        int $limit,
        int $windowSeconds,
        int $bucketCapacity,
        int $refillRate,
    ): Response {
        $identifier = $this->resolveIdentifier($request);
        $key = sha1($identifier . '|' . $request->path());

        if (!TokenBucketLimiter::allow($key, $bucketCapacity, $refillRate)) {
            return $this->tooManyRequests('Too many requests (burst limit exceeded)');
        }
        if (!SlidingWindowLimiter::handle($key, $limit, $windowSeconds)) {
            return $this->tooManyRequests('Too many requests (rate limit exceeded)');
        }

        return $next($request);
    }

    public function resolveIdentifier(Request $request)
    {
        if ($request->user()) {
            return 'user:' . $request->user()->id;
        }

        if ($request->header('X-API-KEY')) {
            return 'api:' . $request->header('X-API-KEY');
        }

        return 'ip:' . $request->ip();
    }

    protected function tooManyRequests(string $message)
    {
        return response()->json([
            'message' => $message,
        ], Response::HTTP_TOO_MANY_REQUESTS);
    }
}
