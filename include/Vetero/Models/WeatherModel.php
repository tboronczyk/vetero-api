<?php
declare(strict_types=1);

namespace Vetero\Models;

use Slim\Container;

/**
 * Class WeatherModel
 * @package Vetero\Models
 */
class WeatherModel extends Model
{
    /**
     * Return the current weather for the given location.
     *
     * Example of returned array:
     * [
     *     "description" => "snow",
     *     "humidity" => 0.87,
     *     "precip_chance" => 0.9,
     *     "temperature" => 29,
     *     "wind_speed" => 15,
     *     "wind_bearing" => 292,
     *     "time" => 1547087718,
     *     "forecast" => [
     *         0 => [
     *             "description" => "snow",
     *             "temperature_high" => 36,
     *             "temperature_low" => 25,
     *             "time" => 1547010000
     *         ],
     *         1 => [
     *             "description" => "snow",
     *             "temp_high" => 26,
     *             "temp_low" => 15,
     *             "time" => 1547096400
     *         ]
     *         2 => [
     *             "description" => "partly-cloudy-day",
     *             "temp_high" => 21,
     *             "temp_low" => 17,
     *             "time" => 1547182800
     *         ]
     *     ]
     * ]
     *
     * @param float $lat
     * @param float $lon
     * @return array
     */
    public function getWeather(float $lat, float $lon): array {
        $weather = $this->queryColumn(
            'SELECT weather FROM weather WHERE lat = ? AND lon = ?',
            [$lat, $lon]
        );

        if (empty($weather)) {
            $response = $this->getWeatherFromApi($lat, $lon);
            $weather = $this->parseApiWeatherResponse($response);

            $this->query(
                'INSERT INTO weather (lat, lon, weather, updated)
                    VALUES (?, ?, ?, ?)',
                [$lat, $lon, json_encode($weather), time()]
            );
        }

        if (is_string($weather)) {
            $weather = json_decode($weather, true);
        }

        return $weather;
    }

    /**
     * Retrieve the current weather for the given location from the API.
     *
     * @param float $lat
     * @param float $lon
     * @return array
     */
    protected function getWeatherFromApi(float $lat, float $lon): array
    {
        $url = 'https://api.darksky.net/forecast/' .
            sprintf('%s/%01.2f,%01.2f', getenv('DARKSKY_API_SECRET'), $lat, $lon) .
            '?lang=en&units=us&exclude=minutely,hourly,alerts,flags';
        $response = file_get_contents($url);

        return json_decode($response, true);
    }

    /**
     * Structure the API's response into a more easily consumable format.
     *
     * @param array $response
     * @return array
     */
    protected function parseApiWeatherResponse(array $response): array {
        $current = $response['currently'];
        $weather = [
            'description' => $current['icon'],
            'humidity' => round($current['humidity'], 2),
            'precip_chance' => round($current['precipProbability'], 2),
            'temperature' => (int)round($current['temperature']),
            'wind_speed' => (int)round($current['windSpeed']),
            'wind_bearing' => ($current['windSpeed'] > 0) ? $current['windBearing'] : null,
            'time' => (int)$current['time']
        ];

        $maxDays = 3;
        for ($i = 0; $i < $maxDays; $i++) {
            $daily = $response['daily']['data'][$i];
            $weather['forecast'][] = [
                'description' => $daily['icon'],
                'temperature_high' => (int)round($daily['temperatureHigh']),
                'temperature_low' => (int)round($daily['temperatureLow']),
                'time' => (int)$daily['time']
            ];
        }

        return $weather;
    }
}
