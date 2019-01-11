<?php
declare(strict_types=1);

namespace Vetero\Controllers;

use Slim\Container;
use Slim\Http\Response;

/**
 * Class Controller
 * @package Vetero\Controllers
 */
class Controller
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
