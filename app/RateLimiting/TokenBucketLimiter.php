<?php

namespace App\RateLimiting;

use Illuminate\Support\Facades\Redis;

class TokenBucketLimiter
{
    public static function allow(string $key, int $capacity, int $refillRate)
    {
        $rediskey = "token_bucket:{$key}";
        $now = microtime(true);

        $bucket = Redis::hgetall($rediskey);
        
        $tokens = $bucket['tokens'] ?? $capacity;
        $lastRefill = $bucket['last_refill'] ?? $now;

        //Refill tokens
        $elapsed = max(0, $now - $lastRefill);
        $tokens = min($capacity, $tokens + ($elapsed * $refillRate));

        if ($tokens < 1) {
            return false;
        }

        // Consume token
        $tokens--;

        Redis::hmset($rediskey, [
            'tokens' => $tokens,
            'last_refill' => $now,
        ]);

        Redis::expire($rediskey, 120);

        return true;
    }
}
