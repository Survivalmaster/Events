<?php

namespace App\Http\Controllers;

use App\Models\AppSetting;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class SettingApiController extends Controller
{
    public function index(): JsonResponse
    {
        $settings = [
            'username' => '',
            'handler' => '',
        ];

        AppSetting::query()
            ->whereIn('setting_key', array_keys($settings))
            ->get()
            ->each(function (AppSetting $setting) use (&$settings): void {
                $settings[$setting->setting_key] = $setting->setting_value ?? '';
            });

        return response()->json(['settings' => $settings]);
    }

    public function store(Request $request): JsonResponse
    {
        $key = trim((string) $request->input('key', ''));
        $value = trim((string) $request->input('value', ''));

        if (! in_array($key, ['username', 'handler'], true)) {
            return response()->json(['error' => 'Invalid setting key'], 422);
        }

        AppSetting::query()->updateOrCreate(
            ['setting_key' => $key],
            ['setting_value' => $value],
        );

        return response()->json(['ok' => true]);
    }
}
