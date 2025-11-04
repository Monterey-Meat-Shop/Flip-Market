<?php

use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\Storage;

if (!function_exists('image_url')) {
    /**
     * Generate the correct public image URL for any given path (auto-handles local vs production).
     * Adds cache-busting (version parameter) based on file last modified time.
     *
     * @param  string|null  $path
     * @param  string|null  $placeholder
     * @return string
     */
    function image_url(?string $path, ?string $placeholder = 'https://via.placeholder.com/300'): string
    {
        if (empty($path)) {
            return $placeholder;
        }

        $path = ltrim($path, '/');

        if (Storage::disk('public')->exists($path)) {
            $baseUrl = config('app.url');
            $mtime = Storage::disk('public')->lastModified($path); // cache-busting timestamp

            if (App::environment('local')) {
                // Local environment (php artisan serve)
                return "{$baseUrl}/storage/{$path}?v={$mtime}";
            }

            // Production (Hostinger uses /uploads/)
            return "{$baseUrl}/uploads/{$path}?v={$mtime}";
        }

        return $placeholder;
    }
}
