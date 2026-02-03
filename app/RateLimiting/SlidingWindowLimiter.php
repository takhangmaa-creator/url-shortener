<?php

namespace App\RateLimiting;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Redis;

class SlidingWindowLimiter
{
    public static function handle(string $key, int $limit, int $windowSeconds)
    {
        $now = microtime(true);
        $windowStart = $now - $windowSeconds;

        $redisKey = "rate_limit:{$key}";

        Redis::pipeline(function ($pipe) use ($redisKey, $now, $windowStart) {
            // Remove old request
            $pipe->zremrangebyscore($redisKey, 0, $windowStart);

            // Add current request
            $pipe->zAdd($redisKey, $now, (string) $now);

            $pipe->expire($redisKey, 60);
        });

        $attempts = Redis::zcard($redisKey);
        return $attempts <= $limit;
    }
}
