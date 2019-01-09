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

    /**
     * Return a server error response.
     *
     * @param Response $resp
     * @return Response
     */
    protected function serverErrorResponse(Response $resp)
    {
        return $resp->withJson(['error' => 'server error occurred'], 500);
    }
}
