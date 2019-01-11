<?php
declare(strict_types=1);

namespace Vetero\Middleware;

use Slim\Container;
use Slim\Http\Request;
use Slim\Http\Response;

/**
 * Class AuthorizationMiddleware
 * @package Vetero\Middleware
 */
class AuthorizationMiddleware extends Middleware
{
    protected $resource;

    /**
     * Constructor
     *
     * @param Container $c
     * @param string $resource
     */
    public function __construct(Container $c, string $resource)
    {
        parent::__construct($c);
        $this->resource = $resource;
    }

    /**
     * Block unauthorized requests.
     *
     * @param Request $req
     * @param Response $resp
     * @param callable $next
     * @return Response
     */
    public function __invoke(Request $req, Response $resp, callable $next)
    {
        $header = $req->getHeaderLine('Authorization');
        $token = substr($header, strlen('Bearer '));

        if ($token && $this->container['AuthorizationModel']->canAccess($token, $this->resource)) {
            return $next($req, $resp);
        }

        usleep(rand(1, 300) * 10000);
        return $resp->withStatus(401)->withHeader('WWW-Authenticate', 'Bearer');
    }
}
