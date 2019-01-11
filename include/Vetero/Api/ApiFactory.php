<?php
declare(strict_types=1);

namespace Vetero\Api;

use Slim\Container;

/**
 * Class ApiFactory
 * @package Vetero\Api
 */
class ApiFactory
{
    /**
     * supported APIs
     */
    const APITYPE_WEATHER = 1;
    const APITYPE_LOCATION = 2;

    protected $container;

    /**
     * Constructor
     *
     * @param Container $c
     */
    public function __construct(Container $c)
    {
        $this->container = $c;
    }

    /**
     * Return an object for interacting with the given type of API.
     *
     * @param int $apiType
     * @return Api
     * @throws \DomainException
     */
    public function getApi(int $apiType): Api
    {
        switch ($apiType) {
            case self::APITYPE_WEATHER:
                return new DarkSky($this->container);

            case self::APITYPE_LOCATION:
                return new GeoNames($this->container);
        }

        throw new \DomainException('Unknown API type');
    }
}
