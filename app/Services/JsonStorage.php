<?php

namespace App\Services;

use Illuminate\Support\Facades\Storage;

class JsonStorage
{
    public static function get(string $file, mixed $default = []): mixed
    {
        if (!Storage::disk('local')->exists($file)) {
            return $default;
        }
        return json_decode(Storage::disk('local')->get($file), true) ?? $default;
    }

    public static function put(string $file, mixed $data): void
    {
        Storage::disk('local')->put($file, json_encode($data, JSON_PRETTY_PRINT));
    }
}
