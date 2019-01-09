<?php
declare(strict_types=1);

namespace Vetero\Models;

use Slim\Container;

/**
 * Class LocationsModel
 * @package Vetero\Models
 */
class LocationsModel extends Model
{
    /**
     * Return naming information for the given location.
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
        $location = $this->queryRow(
            'SELECT name, region, country FROM locations WHERE lat = ? AND lon = ?',
            [$lat, $lon]
        );
        if (empty($location)) {
            $response = $this->getLocationFromApi($lat, $lon);
            $location = $this->parseApiResponse($response);

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
        }
        return $location;
    }

    /**
     * Retrieve the name information for the given location from the API.
     *
     * @param float $lat
     * @param float $lon
     * @return array
     */
    protected function getLocationFromApi(float $lat, float $lon): array
    {
        $url = 'https://secure.geonames.org/findNearbyPlaceNameJSON' .
            sprintf('?lat=%01.2f&lng=%01.2f&username=%s', $lat, $lon, getenv('GEONAMES_API_USERNAME'));
        $response = file_get_contents($url);

        return json_decode($response, true);
    }

    /**
     * Structure the API's response into a more easily consumable format.
     *
     * @param array $response
     * @return array
     */
    protected function parseApiResponse(array $response): array
    {
        return [
            'name' => $response['geonames'][0]['name'],
            'region' => $response['geonames'][0]['adminName1'],
            'country' => $response['geonames'][0]['countryCode']
        ];
    }
}
