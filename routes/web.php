<?php

use App\Http\Controllers\EnvironmentalEventApiController;
use App\Http\Controllers\EventApiController;
use App\Http\Controllers\ImageCacheController;
use App\Http\Controllers\SettingApiController;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('dashboard');
})->name('dashboard');

Route::view('/index.html', 'dashboard');
Route::view('/events.html', 'events');
Route::view('/events-wizard.html', 'events-wizard');
Route::view('/environmental-events.html', 'environmental-events');
Route::view('/environmental-events-wizard.html', 'environmental-events-wizard');

Route::get('/api/events.php', [EventApiController::class, 'index']);
Route::post('/api/events.php', [EventApiController::class, 'store']);
Route::delete('/api/events.php', [EventApiController::class, 'destroy']);

Route::get('/api/environmental-events.php', [EnvironmentalEventApiController::class, 'index']);
Route::post('/api/environmental-events.php', [EnvironmentalEventApiController::class, 'store']);
Route::delete('/api/environmental-events.php', [EnvironmentalEventApiController::class, 'destroy']);

Route::get('/api/settings.php', [SettingApiController::class, 'index']);
Route::post('/api/settings.php', [SettingApiController::class, 'store']);

Route::get('/api/image-cache.php', [ImageCacheController::class, 'show']);
Route::match(['get', 'post'], '/api/image-cache-warm.php', [ImageCacheController::class, 'warm']);
