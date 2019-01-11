<?php
declare(strict_types=1);

namespace Vetero\Models;

use Slim\Container;
use Vetero\Api\DarkSky;

/**
 * Class WeatherModel
 * @package Vetero\Models
 */
class WeatherModel extends Model
{
    /**
     * Return the current weather for a given location.
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
     *         ],
     *         2 => [
     *             "description" => "partly-cloudy-day",
     *             "temp_high" => 21,
     *             "temp_low" => 17,
     *             "time" => 1547182800
     *         ],
     *         ...
     *     ]
     * ]
     *
     * @param float $lat
     * @param float $lon
     * @return array
     */
    public function getWeather(float $lat, float $lon): array
    {
        $this->logger->info('Weather requested', ['lat' => $lat, 'lon' => $lon]);

        try {
            $weather = $this->queryColumn(
                'SELECT weather FROM weather WHERE lat = ? AND lon = ?',
                [$lat, $lon]
            );
        } catch (\PDOException $e) {
            $this->logger->error(
                'Failed to retrieve weather from database',
                ['lat' => $lat, 'lon' => $lon, 'msg' => $e->getMessage()]
            );
        }
        if (!empty($weather)) {
            $this->logger->info('Weather returned from database');
            return json_decode($weather, true);
        }

        // existing weather data was not found in the database - retrieve it
        // from the API
        try {
            $weather = $this->getWeatherFromApi($lat, $lon);
        } catch (\Exception $e) {
            $this->logger->error(
                'Network error retrieving weather from API',
                ['lat' => $lat, 'lon' => $lon, 'msg' => $e->getMessage()]
            );
            return [];
        }
        if (empty($weather)) {
            return [];
        }

        // save the weather data for future lookups
        try {
            $this->query(
                'INSERT INTO weather (lat, lon, weather, updated)
                VALUES (?, ?, ?, ?)',
                [$lat, $lon, json_encode($weather), time()]
            );
        } catch (\PDOException $e) {
            $this->logger->error(
                'Failed to save weather to database',
                ['lat' => $lat, 'lon' => $lon, 'msg' => $e->getMessage()]
            );
        }

        $this->logger->info('Weather returned from API');
        return $weather;
    }

    /**
     * Retrieve the current weather for a given location from the API.
     *
     * @param float $lat
     * @param float $lon
     * @return array
     * @throws \GuzzleHttp\Exception\ConnectException on network error
     */
    protected function getWeatherFromApi(float $lat, float $lon): array
    {
        $api = $this->container['DarkSkyApi'];
        try {
            $resp = $api->getWeather($lat, $lon);
        } catch (\GuzzleHttp\Exception\BadResponseException $e) {
            // catches 4xx-5xx status range - network errors are not caught
            $this->logger->error(
                'Failed to retrieve weather from API',
                ['lat' => $lat, 'lon' => $lon, 'msg' => $e->getMessage()]
            );
            return [];
        }

        $body = json_decode((string)$resp->getBody(), true);

        $weather = $this->parseApiWeatherResponse($body);
        return $weather;
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

        $maxDays = 5;
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
