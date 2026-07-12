<?php

namespace App\Http\Controllers;

use App\Models\Event;
use App\Support\EventPortalSchema;
use App\Support\ImageCache;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class EventApiController extends Controller
{
    public function index(): JsonResponse
    {
        EventPortalSchema::ensure();

        $events = Event::query()
            ->orderByDesc('created_at')
            ->orderByDesc('id')
            ->get()
            ->map(fn (Event $event): array => $this->formatEvent($event));

        return response()->json(['events' => $events]);
    }

    public function store(Request $request): JsonResponse
    {
        EventPortalSchema::ensure();

        $payload = $request->all();

        $id = isset($payload['id']) ? (int) $payload['id'] : null;
        $propertyId = trim((string) ($payload['propertyId'] ?? $payload['property_id'] ?? ''));
        $name = trim((string) ($payload['name'] ?? ''));
        $date = trim((string) ($payload['date'] ?? ''));
        $time = trim((string) ($payload['time'] ?? ''));

        if ($name === '' || $date === '' || $time === '' || $propertyId === '') {
            return response()->json(['error' => 'Missing required fields'], 422);
        }

        $event = $id ? Event::query()->find($id) : new Event();
        if ($id && ! $event) {
            return response()->json(['error' => 'Event not found'], 404);
        }

        $event->fill([
            'status' => $this->stringValue($payload, 'status', 'NEW'),
            'handler' => $this->stringValue($payload, 'handler'),
            'type' => $this->stringValue($payload, 'type'),
            'event_date' => $date,
            'event_time' => $time,
            'name' => $name,
            'district' => $this->stringValue($payload, 'district'),
            'discord' => $this->stringValue($payload, 'discord'),
            'description' => $this->stringValue($payload, 'description'),
            'property_id' => $propertyId,
            'notes' => $this->stringValue($payload, 'notes'),
        ]);

        if (! $event->exists) {
            $event->created_at = $this->stringValue($payload, 'createdAt') ?: now();
        }

        foreach (['bannerUrl', 'banner_url'] as $key) {
            if (array_key_exists($key, $payload)) {
                $event->banner_url = trim((string) $payload[$key]);
                break;
            }
        }

        $event->banner_pos_x = $this->clampInt($payload['bannerPosX'] ?? $payload['banner_pos_x'] ?? $event->banner_pos_x ?? 50, 0, 100);
        $event->banner_pos_y = $this->clampInt($payload['bannerPosY'] ?? $payload['banner_pos_y'] ?? $event->banner_pos_y ?? 50, 0, 100);
        $event->banner_zoom = $this->clampFloat($payload['bannerZoom'] ?? $payload['banner_zoom'] ?? $event->banner_zoom ?? 1.0, 0.5, 3.0);

        $event->save();

        return response()->json(['ok' => true, 'id' => $event->id], $id ? 200 : 201);
    }

    public function destroy(Request $request): JsonResponse
    {
        EventPortalSchema::ensure();

        $id = (int) $request->query('id');
        if ($id <= 0) {
            return response()->json(['error' => 'Valid id is required'], 422);
        }

        $deleted = Event::query()->whereKey($id)->delete();
        if (! $deleted) {
            return response()->json(['error' => 'Event not found'], 404);
        }

        return response()->json(['ok' => true]);
    }

    private function formatEvent(Event $event): array
    {
        return [
            'id' => $event->id,
            'createdAt' => optional($event->created_at)->format('Y-m-d H:i:s'),
            'status' => $event->status,
            'handler' => $event->handler,
            'type' => $event->type,
            'date' => $event->event_date,
            'time' => $event->event_time,
            'name' => $event->name,
            'district' => $event->district,
            'discord' => $event->discord,
            'bannerUrl' => $event->banner_url,
            'banner_url' => $event->banner_url,
            'bannerCachedUrl' => ImageCache::publicUrlIfCached((string) $event->banner_url),
            'banner_cached_url' => ImageCache::publicUrlIfCached((string) $event->banner_url),
            'bannerPosX' => $event->banner_pos_x,
            'banner_pos_x' => $event->banner_pos_x,
            'bannerPosY' => $event->banner_pos_y,
            'banner_pos_y' => $event->banner_pos_y,
            'bannerZoom' => $event->banner_zoom,
            'banner_zoom' => $event->banner_zoom,
            'description' => $event->description,
            'propertyId' => $event->property_id,
            'property_id' => $event->property_id,
            'notes' => $event->notes,
        ];
    }

    private function stringValue(array $payload, string $key, string $default = ''): string
    {
        return trim((string) ($payload[$key] ?? $default));
    }

    private function clampInt(mixed $value, int $min, int $max): int
    {
        return max($min, min($max, (int) $value));
    }

    private function clampFloat(mixed $value, float $min, float $max): float
    {
        return max($min, min($max, (float) $value));
    }
}
