<?php
declare(strict_types=1);

namespace Vetero\Api;

use Slim\Container;
use Psr\Http\Message\ResponseInterface;

/**
 * Class Api
 * @package Vetero\Api
 */
class Api
{
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
     * Perform a GET request.
     *
     * @param string $url
     * @return ResponseInterface
     */
    protected function doGet(string $url): ResponseInterface
    {
        return $this->container['GuzzleClient']->request('GET', $url);
    }
}
