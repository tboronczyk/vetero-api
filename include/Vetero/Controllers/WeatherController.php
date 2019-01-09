<?php
declare(strict_types=1);

namespace Vetero\Controllers;

use Slim\Http\Request;
use Slim\Http\Response;

/**
 * Class WeatherController
 * @package Vetero\Controllers
 */
class WeatherController extends Controller
{
    /**
     * Return the current weather for a given location.
     *
     * @param Request $req
     * @param Response $resp
     * @param array $args
     * @return Response
     */
    public function getWeather(Request $req, Response $resp, array $args): Response
    {
        // two decimal places is sufficient
        $lat = round(floatval($args['lat']), 2);
        $lon = round(floatval($args['lon']), 2);

        $location = $this->container->get('LocationsModel')->getLocation($lat, $lon);
        $weather = $this->container->get('WeatherModel')->getWeather($lat, $lon);

        return $resp->withJson([
            'location' => $location,
            'weather' => $weather
        ]);
    }
}
