<?php

namespace App\Http\Controllers;

use App\Models\EnvironmentalEvent;
use App\Support\EventPortalSchema;
use App\Support\ImageCache;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Schema;

class EnvironmentalEventApiController extends Controller
{
    public function index(): JsonResponse
    {
        EventPortalSchema::ensure();

        $query = EnvironmentalEvent::query();

        if (Schema::hasColumn('environmental_events', 'created_at')) {
            $query->orderByDesc('created_at');
        }

        $query->orderByDesc('id');

        $events = $query->get()
            ->map(fn (EnvironmentalEvent $event): array => $this->formatEvent($event));

        return response()->json(['events' => $events]);
    }

    public function store(Request $request): JsonResponse
    {
        EventPortalSchema::ensure();

        $payload = $request->all();
        $id = isset($payload['id']) ? (int) $payload['id'] : null;
        $name = trim((string) ($payload['name'] ?? ''));

        if ($name === '') {
            return response()->json(['error' => 'Missing required fields'], 422);
        }

        $event = $id ? EnvironmentalEvent::query()->find($id) : new EnvironmentalEvent();
        if ($id && ! $event) {
            return response()->json(['error' => 'Event not found'], 404);
        }

        $event->fill([
            'event_id' => $this->stringValue($payload, 'eventId') ?: $this->stringValue($payload, 'event_id'),
            'faction_flags' => $this->stringValue($payload, 'factionFlags') ?: $this->stringValue($payload, 'faction_flags'),
            'weight' => $this->clampInt($payload['weight'] ?? 5, 1, 10),
            'type' => $this->stringValue($payload, 'type'),
            'name' => $name,
            'district' => $this->stringValue($payload, 'district'),
            'label' => $this->stringValue($payload, 'label') ?: $this->stringValue($payload, 'description'),
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

        $deleted = EnvironmentalEvent::query()->whereKey($id)->delete();
        if (! $deleted) {
            return response()->json(['error' => 'Event not found'], 404);
        }

        return response()->json(['ok' => true]);
    }

    private function formatEvent(EnvironmentalEvent $event): array
    {
        return [
            'id' => $event->id,
            'createdAt' => optional($event->created_at)->format('Y-m-d H:i:s'),
            'eventId' => $event->event_id,
            'event_id' => $event->event_id,
            'factionFlags' => $event->faction_flags,
            'faction_flags' => $event->faction_flags,
            'weight' => $event->weight,
            'type' => $event->type,
            'name' => $event->name,
            'district' => $event->district,
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
            'label' => $event->label,
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
