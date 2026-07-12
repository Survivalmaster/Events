<?php

namespace App\Support;

use Illuminate\Support\Facades\File;
use Illuminate\Support\Str;

class ImageCache
{
    public const CACHE_TTL_SECONDS = 2592000;
    private const MAX_BYTES = 8388608;

    public static function publicUrlIfCached(string $url): ?string
    {
        if (! self::isAllowedUrl($url)) {
            return null;
        }

        $path = self::findCachedImage(self::cacheDir(), sha1($url));

        if (! $path) {
            return null;
        }

        return asset('cached-images/'.basename($path));
    }

    public static function ensure(string $url): ?array
    {
        if (! self::isAllowedUrl($url)) {
            return null;
        }

        $cacheDir = self::cacheDir();
        File::ensureDirectoryExists($cacheDir);

        $hash = sha1($url);
        $metaPath = "{$cacheDir}/{$hash}.json";
        $existingPath = self::findCachedImage($cacheDir, $hash);

        if ($existingPath && filemtime($existingPath) > time() - self::CACHE_TTL_SECONDS) {
            return [
                'path' => $existingPath,
                'mime_type' => self::readMimeType($metaPath),
                'url' => asset('cached-images/'.basename($existingPath)),
                'fresh' => false,
            ];
        }

        $download = self::downloadImage($url);
        if (! $download && $existingPath) {
            return [
                'path' => $existingPath,
                'mime_type' => self::readMimeType($metaPath),
                'url' => asset('cached-images/'.basename($existingPath)),
                'fresh' => false,
            ];
        }

        if (! $download) {
            return null;
        }

        if ($existingPath) {
            File::delete($existingPath);
        }

        $extension = self::extensionForMimeType($download['mime_type']);
        $imagePath = "{$cacheDir}/{$hash}.{$extension}";

        File::put($imagePath, $download['contents']);
        File::put($metaPath, json_encode([
            'url' => $url,
            'mime_type' => $download['mime_type'],
            'cached_at' => now()->toIso8601String(),
        ], JSON_PRETTY_PRINT));

        return [
            'path' => $imagePath,
            'mime_type' => $download['mime_type'],
            'url' => asset('cached-images/'.basename($imagePath)),
            'fresh' => true,
        ];
    }

    public static function isAllowedUrl(string $url): bool
    {
        if ($url === '') {
            return false;
        }

        $parts = parse_url($url);
        if (! is_array($parts) || ! in_array($parts['scheme'] ?? '', ['http', 'https'], true)) {
            return false;
        }

        $host = $parts['host'] ?? '';

        return $host !== '' && ! in_array(Str::lower($host), ['localhost', '127.0.0.1', '::1'], true);
    }

    public static function cacheDir(): string
    {
        return public_path('cached-images');
    }

    private static function findCachedImage(string $cacheDir, string $hash): ?string
    {
        $matches = glob("{$cacheDir}/{$hash}.*") ?: [];

        foreach ($matches as $match) {
            if (! str_ends_with($match, '.json')) {
                return $match;
            }
        }

        return null;
    }

    private static function downloadImage(string $url): ?array
    {
        if (function_exists('curl_init')) {
            $download = self::downloadImageWithCurl($url);
            if ($download) {
                return $download;
            }
        }

        $context = stream_context_create([
            'http' => [
                'follow_location' => 1,
                'ignore_errors' => true,
                'max_redirects' => 3,
                'timeout' => 8,
                'user_agent' => 'EventsPortalImageCache/1.0',
            ],
        ]);

        $contents = @file_get_contents($url, false, $context, 0, self::MAX_BYTES + 1);
        if ($contents === false || strlen($contents) === 0 || strlen($contents) > self::MAX_BYTES) {
            return null;
        }

        $mimeType = self::detectMimeType($contents);
        if (! str_starts_with($mimeType, 'image/')) {
            return null;
        }

        return [
            'contents' => $contents,
            'mime_type' => $mimeType,
        ];
    }

    private static function downloadImageWithCurl(string $url): ?array
    {
        $handle = curl_init($url);
        if ($handle === false) {
            return null;
        }

        $contents = '';

        curl_setopt_array($handle, [
            CURLOPT_FOLLOWLOCATION => true,
            CURLOPT_MAXREDIRS => 3,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_TIMEOUT => 10,
            CURLOPT_USERAGENT => 'EventsPortalImageCache/1.0',
            CURLOPT_WRITEFUNCTION => function ($handle, string $chunk) use (&$contents): int {
                $contents .= $chunk;

                if (strlen($contents) > self::MAX_BYTES) {
                    return 0;
                }

                return strlen($chunk);
            },
        ]);

        curl_exec($handle);
        $statusCode = (int) curl_getinfo($handle, CURLINFO_RESPONSE_CODE);
        $contentType = (string) curl_getinfo($handle, CURLINFO_CONTENT_TYPE);
        $errorNumber = curl_errno($handle);
        curl_close($handle);

        if ($errorNumber !== 0 || $statusCode < 200 || $statusCode >= 300 || $contents === '') {
            return null;
        }

        $mimeType = trim(explode(';', $contentType)[0]) ?: self::detectMimeType($contents);
        if (! str_starts_with($mimeType, 'image/')) {
            $mimeType = self::detectMimeType($contents);
        }

        if (! str_starts_with($mimeType, 'image/')) {
            return null;
        }

        return [
            'contents' => $contents,
            'mime_type' => $mimeType,
        ];
    }

    private static function detectMimeType(string $contents): string
    {
        $info = new \finfo(FILEINFO_MIME_TYPE);

        return $info->buffer($contents) ?: 'application/octet-stream';
    }

    private static function extensionForMimeType(string $mimeType): string
    {
        return match ($mimeType) {
            'image/jpeg' => 'jpg',
            'image/png' => 'png',
            'image/gif' => 'gif',
            'image/webp' => 'webp',
            'image/svg+xml' => 'svg',
            default => 'img',
        };
    }

    private static function readMimeType(string $metaPath): string
    {
        if (! File::exists($metaPath)) {
            return 'application/octet-stream';
        }

        $meta = json_decode((string) File::get($metaPath), true);

        return is_array($meta) ? (string) ($meta['mime_type'] ?? 'application/octet-stream') : 'application/octet-stream';
    }
}
