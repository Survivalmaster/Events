<?php

namespace App\Http\Controllers;

use App\Models\EnvironmentalEvent;
use App\Models\Event;
use App\Support\EventPortalSchema;
use App\Support\ImageCache;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class ImageCacheController extends Controller
{
    public function show(Request $request): Response
    {
        $url = trim((string) $request->query('url', ''));
        $cached = ImageCache::ensure($url);

        if (! $cached) {
            abort(404);
        }

        return response()->file($cached['path'], [
            'Content-Type' => $cached['mime_type'],
            'Cache-Control' => 'public, max-age='.ImageCache::CACHE_TTL_SECONDS,
        ]);
    }

    public function warm(): JsonResponse
    {
        EventPortalSchema::ensure();

        $urls = Event::query()
            ->where('banner_url', '<>', '')
            ->pluck('banner_url')
            ->merge(
                EnvironmentalEvent::query()
                    ->where('banner_url', '<>', '')
                    ->pluck('banner_url'),
            )
            ->map(fn ($url): string => trim((string) $url))
            ->filter()
            ->unique()
            ->values();

        $cached = 0;
        $failed = 0;

        foreach ($urls as $url) {
            if (ImageCache::ensure($url)) {
                $cached += 1;
            } else {
                $failed += 1;
            }
        }

        return response()->json([
            'ok' => true,
            'total' => $urls->count(),
            'cached' => $cached,
            'failed' => $failed,
        ]);
    }
}
