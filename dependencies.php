<?php
declare(strict_types=1);

use Slim\App;
use Slim\Container;
use GuzzleHttp\Client as GuzzleClient;
use Monolog\Logger;
use Monolog\Handler\ErrorLogHandler;
use Monolog\Formatter\LineFormatter;
use Vetero\Api\DarkSkyApi;
use Vetero\Api\GeoNamesApi;
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

$c['Logger'] = function (Container $c): Logger {
    $debugLevel = (getenv('DEBUG') == 'true') ? Logger::DEBUG : Logger::ERROR;
    $formatter = new LineFormatter(null, null, false, true);

    $errorLogHandler = new ErrorLogHandler();
    $errorLogHandler->setFormatter($formatter);
    $errorLogHandler->setLevel($debugLevel);

    $logger = new Logger('vetero-api');
    $logger->pushHandler($errorLogHandler);
    return $logger;
};

$c['DarkSkyApi'] = function (Container $c): DarkSkyApi {
    return new DarkSkyApi($c);
};

$c['GeoNamesApi'] = function (Container $c): GeoNamesApi {
    return new GeoNamesApi($c);
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
