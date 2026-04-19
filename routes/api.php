<?php

use App\Http\Controllers\Api\MenuController as ApiMenuController;
use App\Http\Controllers\Api\OrderController as ApiOrderController;
use App\Services\RecommendationService;
use App\Http\Middleware\EnsureUserIsNotBanned;
use App\Services\WeatherService;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| All routes are prefixed with /api
|
*/

// Public routes - Weather & Recommendations (rate-limited to prevent abuse)
Route::middleware([
    'web',
    'auth',
    EnsureUserIsNotBanned::class,
    'throttle:30,1'])->group(function () {
    Route::get('/weather', function (WeatherService $weatherService) {
        $lat = request('lat');
        $lon = request('lon');

        $weather = $weatherService->getCurrentWeather(
            $lat ? (float) $lat : null,
            $lon ? (float) $lon : null
        );

        return response()->json($weather);
    })->name('api.weather');

    Route::get('/recommend', function (RecommendationService $recommendationService) {
        $input = request('q');
        $lat = request('lat');
        $lon = request('lon');

        $recommendations = $recommendationService->getRecommendations(
            $input,
            $lat ? (float) $lat : null,
            $lon ? (float) $lon : null
        );

        return response()->json($recommendations);
    })->name('api.recommend');

    Route::get('/drinks', function (RecommendationService $recommendationService) {
        $query = request('q');

        $drinks = $query
            ? $recommendationService->searchDrinks($query)
            : $recommendationService->getAllDrinks();

        return response()->json(array_values($drinks));
    })->name('api.drinks');
});

// Protected routes - requires authentication (web guard) + ban check
Route::middleware(['web', 'auth', EnsureUserIsNotBanned::class, 'throttle:60,1'])->group(function () {
    // Orders
    Route::get('/orders', [ApiOrderController::class, 'index'])->name('api.orders');
    Route::get('/orders/{id}', [ApiOrderController::class, 'show'])->name('api.orders.show');

    // Menu
    Route::get('/menu', [ApiMenuController::class, 'index'])->name('api.menu');
    Route::get('/menu/{id}', [ApiMenuController::class, 'show'])->name('api.menu.show');
});
