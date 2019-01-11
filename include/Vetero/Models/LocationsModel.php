<?php
declare(strict_types=1);

namespace Vetero\Models;

use Slim\Container;
use Vetero\Api\GeoNames;

/**
 * Class LocationsModel
 * @package Vetero\Models
 */
class LocationsModel extends Model
{
    /**
     * Return naming information for a given location.
     *
     * Example of returned array:
     * [
     *     "name" => "Syracuse",
     *     "region" => "New York",
     *     "country" => "US"
     * ]
     *
     * @param float $lat
     * @param float $lon
     * @return array
     */
    public function getLocation(float $lat, float $lon): array
    {
        $this->logger->info('Location requested', ['lat' => $lat, 'lon' => $lon]);

        try {
        $location = $this->queryRow(
            'SELECT name, region, country FROM locations WHERE lat = ? AND lon = ?',
            [$lat, $lon]
        );
        } catch (\PDOException $e) {
            $this->logger->error(
                'Failed to retrieve location from database',
                ['lat' => $lat, 'lon' => $lon, 'msg' => $e->getMessage()]
            );
        }
        if (!empty($location)) {
            $this->logger->info('Location returned from database');
            return $location;
        }

        // existing location data was not found in the database - retrieve it
        // from the API
        try {
            $location = $this->getLocationFromApi($lat, $lon);
        } catch (\Exception $e) {
            $this->logger->error(
                'Network error retrieving location from API',
                ['lat' => $lat, 'lon' => $lon, 'msg' => $e->getMessage()]
            );
            return [];
        }
        if (empty($location)) {
            return [];
        }

        // save the location data for future lookups
        try {
            $this->query(
                'INSERT INTO locations (lat, lon, name, region, country) VALUES (?, ?, ?, ?, ?)',
                [
                    $lat,
                    $lon,
                    $location['name'],
                    $location['region'],
                    $location['country']
                ]
            );
        } catch (\PDOException $e) {
            $this->logger->error(
                'Failed to save location to database',
                ['lat' => $lat, 'lon' => $lon, 'msg' => $e->getMessage()]
            );
        }

        $this->logger->info('Location returned from API');
        return $location;
    }

    /**
     * Retrieve the name information for a given location from the API.
     *
     * @param float $lat
     * @param float $lon
     * @return array
     * @throws \GuzzleHttp\Exception\ConnectException on network error
     */
    protected function getLocationFromApi(float $lat, float $lon): array
    {
        $api = $this->container['GeoNamesApi'];
        $resp = $api->getLocation($lat, $lon);

        // GeoNames always returns status 200 so we must inspect the response body
        $body = json_decode((string)$resp->getBody(), true);
        if (empty($body['geonames'])) {
            $this->logger->error(
                'Failed to retrieve location from API',
                ['lat' => $lat, 'lon' => $lon, 'msg' => $body['status'] ?? 'emptry result']
            );
            return [];
        }

        $location = $this->parseApiLocationResponse($body['geonames'][0]);
        return $location;
    }

    /**
     * Structure the API's response into a more easily consumable format.
     *
     * @param array $response
     * @return array
     */
    protected function parseApiLocationResponse(array $response): array
    {
        return [
            'name' => $response['name'],
            'region' => $response['adminName1'],
            'country' => $response['countryCode']
        ];
    }
}
