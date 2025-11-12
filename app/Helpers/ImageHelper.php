<?php

use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\Storage;

if (!function_exists('image_url')) {
    /**
     * Generate the correct public image URL for any given path.
     * Works for both local (artisan serve) and Hostinger production.
     */
    function image_url(?string $path, ?string $placeholder = 'https://via.placeholder.com/300'): string
    {
        if (empty($path)) {
            return $placeholder;
        }

        $path = ltrim($path, '/');

        // Check if file exists on the public disk
        if (Storage::disk('public')->exists($path)) {
            $baseUrl = config('app.url');
            $mtime = Storage::disk('public')->lastModified($path); // cache busting

            // Always return /storage/ for both local and production
            return "{$baseUrl}/storage/{$path}?v={$mtime}";
        }

        return $placeholder;
    }
}