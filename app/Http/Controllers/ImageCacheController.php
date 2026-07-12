<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Str;
use Symfony\Component\HttpFoundation\Response;

class ImageCacheController extends Controller
{
    private const CACHE_TTL_SECONDS = 2592000;
    private const MAX_BYTES = 8388608;

    public function show(Request $request): Response
    {
        $url = trim((string) $request->query('url', ''));

        if (! $this->isAllowedUrl($url)) {
            abort(404);
        }

        $cacheDir = storage_path('app/image-cache');
        File::ensureDirectoryExists($cacheDir);

        $hash = sha1($url);
        $metaPath = "{$cacheDir}/{$hash}.json";
        $existingPath = $this->findCachedImage($cacheDir, $hash);

        if ($existingPath && filemtime($existingPath) > time() - self::CACHE_TTL_SECONDS) {
            return $this->fileResponse($existingPath, $this->readMimeType($metaPath));
        }

        $download = $this->downloadImage($url);
        if (! $download && $existingPath) {
            return $this->fileResponse($existingPath, $this->readMimeType($metaPath));
        }

        if (! $download) {
            abort(404);
        }

        if ($existingPath) {
            File::delete($existingPath);
        }

        $extension = $this->extensionForMimeType($download['mime_type']);
        $imagePath = "{$cacheDir}/{$hash}.{$extension}";

        File::put($imagePath, $download['contents']);
        File::put($metaPath, json_encode([
            'url' => $url,
            'mime_type' => $download['mime_type'],
            'cached_at' => now()->toIso8601String(),
        ], JSON_PRETTY_PRINT));

        return $this->fileResponse($imagePath, $download['mime_type']);
    }

    private function isAllowedUrl(string $url): bool
    {
        if ($url === '') {
            return false;
        }

        $parts = parse_url($url);
        if (! is_array($parts) || ! in_array($parts['scheme'] ?? '', ['http', 'https'], true)) {
            return false;
        }

        $host = $parts['host'] ?? '';
        if ($host === '' || in_array(Str::lower($host), ['localhost', '127.0.0.1', '::1'], true)) {
            return false;
        }

        return true;
    }

    private function findCachedImage(string $cacheDir, string $hash): ?string
    {
        $matches = glob("{$cacheDir}/{$hash}.*") ?: [];

        foreach ($matches as $match) {
            if (! str_ends_with($match, '.json')) {
                return $match;
            }
        }

        return null;
    }

    private function downloadImage(string $url): ?array
    {
        if (function_exists('curl_init')) {
            $download = $this->downloadImageWithCurl($url);
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

        $mimeType = $this->detectMimeType($contents);
        if (! str_starts_with($mimeType, 'image/')) {
            return null;
        }

        return [
            'contents' => $contents,
            'mime_type' => $mimeType,
        ];
    }

    private function downloadImageWithCurl(string $url): ?array
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
                $contents = ($contents ?? '').$chunk;

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

        $mimeType = trim(explode(';', $contentType)[0]) ?: $this->detectMimeType($contents);
        if (! str_starts_with($mimeType, 'image/')) {
            $mimeType = $this->detectMimeType($contents);
        }

        if (! str_starts_with($mimeType, 'image/')) {
            return null;
        }

        return [
            'contents' => $contents,
            'mime_type' => $mimeType,
        ];
    }

    private function detectMimeType(string $contents): string
    {
        $info = new \finfo(FILEINFO_MIME_TYPE);

        return $info->buffer($contents) ?: 'application/octet-stream';
    }

    private function extensionForMimeType(string $mimeType): string
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

    private function readMimeType(string $metaPath): string
    {
        if (! File::exists($metaPath)) {
            return 'application/octet-stream';
        }

        $meta = json_decode((string) File::get($metaPath), true);

        return is_array($meta) ? (string) ($meta['mime_type'] ?? 'application/octet-stream') : 'application/octet-stream';
    }

    private function fileResponse(string $path, string $mimeType): Response
    {
        return response()->file($path, [
            'Content-Type' => $mimeType,
            'Cache-Control' => 'public, max-age='.self::CACHE_TTL_SECONDS,
        ]);
    }
}
