<?php
declare(strict_types=1);

use Slim\App;
use Slim\Container;
use GuzzleHttp\Client as GuzzleClient;
use Vetero\Api\ApiFactory;
use Vetero\Controllers\WeatherController;
use Vetero\Middleware\AuthorizationMiddleware;
use Vetero\Models\AuthorizationModel;
use Vetero\Models\LocationsModel;
use Vetero\Models\WeatherModel;

// All object instantiation and initialization should happen in this file.

$c = new Container(['settings' => [
    'displayErrorDetails' => getenv('DEBUG')
]]);

$c['App'] = function (Container $c): App {
    $app = new App($c);
    require_once 'routes/api.php';
    return $app;
};

$c['db'] = function (Container $c): PDO {
    $pdo = new PDO(getenv('DB_DSN'), getenv('DB_USERNAME'), getenv('DB_PASSWORD'));
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    return $pdo;
};

$c['GuzzleClient'] = function (Container $c): GuzzleClient {
    return new GuzzleClient([
        'timeout' => getenv('HTTP_REQUEST_TIMEOUT')
    ]);
};

$c['ApiFactory'] = function (Container $c): ApiFactory {
    return new ApiFactory($c);
};

$c['WeatherController'] = function (Container $c): WeatherController {
    return new WeatherController($c);
};

$c['AuthorizationMiddleware'] = function (Container $c) {
    return function (string $resource) use ($c): AuthorizationMiddleware {
        return new AuthorizationMiddleware($c, $resource);
    };
};

$c['AuthorizationModel'] = function (Container $c): AuthorizationModel {
    return new AuthorizationModel($c);
};

$c['LocationsModel'] = function (Container $c): LocationsModel {
    return new LocationsModel($c);
};

$c['WeatherModel'] = function (Container $c): WeatherModel {
    return new WeatherModel($c);
};

return $c;
