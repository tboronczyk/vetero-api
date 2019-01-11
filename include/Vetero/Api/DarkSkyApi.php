<?php
declare(strict_types=1);

namespace Vetero\Api;

use Psr\Http\Message\ResponseInterface;

/**
 * Class DarkSkyApi
 * @package Vetero\Api
 */
class DarkSkyApi extends Api
{
    /**
     * Retrieve the current weather for the given location.
     *
     * @param float $lat
     * @param float $lon
     * @return ResponseInterface
     */
    public function getWeather(float $lat, float $lon): ResponseInterface
    {
        $url = 'https://api.darksky.net/forecast/' .
            sprintf('%s/%01.2f,%01.2f', getenv('DARKSKY_API_SECRET'), $lat, $lon) .
            '?lang=en&units=us&exclude=minutely,hourly,alerts,flags';

        return $this->doGet($url);
    }
}