<?php
declare(strict_types=1);

namespace Vetero\Middleware;

use Slim\Container;

/**
 * Class Middleware
 * @package Vetero\Middleware
 */
class Middleware
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
}
