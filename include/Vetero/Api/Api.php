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

    /** @var \Monolog\Logger */
    protected $logger;

    /**
     * Constructor
     *
     * @param Container $c
     */
    public function __construct(Container $c)
    {
        $this->container = $c;
        $this->logger = $c->get('Logger');
    }

    /**
     * Perform a GET request.
     *
     * @param string $url
     * @return ResponseInterface
     */
    protected function doGet(string $url): ResponseInterface
    {
        $this->logger->debug('Performing GET request', ['url' => $url]);
        return $this->container['GuzzleClient']->request('GET', $url);
    }
}
