<?php

use App\Http\Controllers\DiscordAuthController;
use App\Http\Controllers\EnvironmentalEventApiController;
use App\Http\Controllers\ImageCacheController;
use App\Http\Controllers\SettingApiController;
use App\Models\EnvironmentalEvent;
use App\Support\EventPortalSchema;
use Illuminate\Support\Facades\Route;

Route::get('/login', [DiscordAuthController::class, 'login'])->name('discord.login');
Route::get('/auth/discord', [DiscordAuthController::class, 'redirect'])->name('discord.redirect');
Route::get('/auth/discord/callback', [DiscordAuthController::class, 'callback'])->name('discord.callback');
Route::post('/logout', [DiscordAuthController::class, 'logout'])->name('discord.logout');

Route::middleware('events.discord')->group(function (): void {
    $dashboard = function () {
        EventPortalSchema::ensure();

        $weights = EnvironmentalEvent::query()
            ->pluck('weight')
            ->map(fn ($weight): int => max(1, min(10, (int) $weight)));

        $total = $weights->count();
        $veryRare = $weights->filter(fn (int $weight): bool => $weight <= 3)->count();
        $rare = $weights->filter(fn (int $weight): bool => $weight >= 4 && $weight <= 7)->count();
        $common = $weights->filter(fn (int $weight): bool => $weight >= 8)->count();

        return view('dashboard', [
            'envStats' => [
                'total' => $total,
                'veryRare' => $veryRare,
                'rare' => $rare,
                'common' => $common,
                'overview' => "Currently tracking {$total} environmental templates. {$veryRare} are very rare (1-3), {$rare} are rare (4-7), and {$common} are common (8-10).",
            ],
        ]);
    };

    Route::get('/', $dashboard)->name('dashboard');

    Route::get('/index.html', $dashboard);
    Route::view('/environmental-events.html', 'environmental-events');
    Route::view('/environmental-events-wizard.html', 'environmental-events-wizard');

    Route::get('/api/environmental-events.php', [EnvironmentalEventApiController::class, 'index']);
    Route::post('/api/environmental-events.php', [EnvironmentalEventApiController::class, 'store']);
    Route::delete('/api/environmental-events.php', [EnvironmentalEventApiController::class, 'destroy']);

    Route::get('/api/settings.php', [SettingApiController::class, 'index']);
    Route::post('/api/settings.php', [SettingApiController::class, 'store']);

    Route::get('/api/image-cache.php', [ImageCacheController::class, 'show']);
    Route::match(['get', 'post'], '/api/image-cache-warm.php', [ImageCacheController::class, 'warm']);
});
