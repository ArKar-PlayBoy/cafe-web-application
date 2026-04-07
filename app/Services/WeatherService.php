<?php

namespace App\Services;

use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;

class WeatherService
{
    private const DEFAULT_LAT = 16.8661;

    private const DEFAULT_LON = 96.1951;

    private const CACHE_TTL = 30;

    public function getCurrentWeather(?float $latitude = null, ?float $longitude = null): array
    {
        $lat = $latitude ?? self::DEFAULT_LAT;
        $lon = $longitude ?? self::DEFAULT_LON;
        $cacheKey = "weather_{$lat}_{$lon}";

        return Cache::remember($cacheKey, self::CACHE_TTL, function () use ($lat, $lon) {
            try {
                $response = Http::connectTimeout(2)->timeout(3)->get('https://api.open-meteo.com/v1/forecast', [
                    'latitude' => $lat,
                    'longitude' => $lon,
                    'current' => 'temperature_2m,relative_humidity_2m,apparent_temperature,weather_code',
                    'timezone' => 'Asia/Yangon',
                ]);

                if (! $response->successful()) {
                    return $this->getDefaultWeather();
                }

                $data = $response->json();
                $current = $data['current'] ?? [];

                return [
                    'temperature' => $current['temperature_2m'] ?? 0,
                    'humidity' => $current['relative_humidity_2m'] ?? 0,
                    'feels_like' => $current['apparent_temperature'] ?? 0,
                    'condition' => $this->getWeatherDescription($current['weather_code'] ?? 0),
                    'code' => $current['weather_code'] ?? 0,
                    'location' => 'Yangon, Myanmar',
                    'cached' => false,
                ];
            } catch (\Exception $e) {
                return $this->getDefaultWeather();
            }
        });
    }

    public function getWeatherDescription(int $code): string
    {
        return match ($code) {
            0 => 'Clear sky',
            1 => 'Mainly clear',
            2 => 'Partly cloudy',
            3 => 'Overcast',
            45, 48 => 'Foggy',
            51, 53, 55 => 'Drizzle',
            56, 57 => 'Freezing drizzle',
            61, 63, 65 => 'Rain',
            66, 67 => 'Freezing rain',
            71, 73, 75 => 'Snow',
            77 => 'Snow grains',
            80, 81, 82 => 'Rain showers',
            85, 86 => 'Snow showers',
            95 => 'Thunderstorm',
            96, 99 => 'Thunderstorm with hail',
            default => 'Unknown',
        };
    }

    private function getDefaultWeather(): array
    {
        return [
            'temperature' => 28,
            'humidity' => 65,
            'feels_like' => 30,
            'condition' => 'Clear sky',
            'code' => 0,
            'location' => 'Yangon, Myanmar',
            'cached' => false,
        ];
    }

    public function isHot(float $temperature): bool
    {
        return $temperature > 25;
    }

    public function isCold(float $temperature): bool
    {
        return $temperature < 15;
    }

    public function isRainy(int $code): bool
    {
        return in_array($code, [51, 53, 55, 56, 57, 61, 63, 65, 66, 67, 80, 81, 82, 95, 96, 99]);
    }
}
