<?php

namespace App\Services;

use App\Models\Url;
use Illuminate\Support\Str;


class UrlService
{
    public function create(array $data): Url
    {
        return Url::create([
            'original_url' => $data['url'],
            'short_code' => $this->generateShortCode(),
            'expires_at' => $data['expires_at'] ?? null
        ]);
    }

    private function generateShortCode()
    {
        do {
            $code = Str::random(6);
        } while (Url::where('short_code', $code)->exists());
        return $code;
    }
}
