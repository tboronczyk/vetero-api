<?php
declare(strict_types=1);

/** @var $app Slim\App */

$app->group('/api', function () use ($app) {
    $app->get('/weather/{lat:-?\d{1,2}\.\d+},{lon:-?\d{1,3}\.\d+}', 'WeatherController:getWeather');
})
    ->add($app->getContainer()->get('AuthorizationMiddleware')('api'));
