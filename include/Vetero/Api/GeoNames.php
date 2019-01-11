<?php
declare(strict_types=1);

namespace Vetero\Api;

use Psr\Http\Message\ResponseInterface;

/**
 * Class GeoNames
 * @package Vetero\Api
 */
class GeoNames extends Api
{
    /**
     * Retrieve the name information for the given location.
     *
     * @param float $lat
     * @param float $lon
     * @return ResponseInterface
     */
    public function getLocation(float $lat, float $lon): ResponseInterface
    {
        $url = 'https://secure.geonames.org/findNearbyPlaceNameJSON' .
            sprintf('?lat=%01.2f&lng=%01.2f&username=%s', $lat, $lon, getenv('GEONAMES_API_USERNAME'));

        return $this->doGet($url);
    }
}