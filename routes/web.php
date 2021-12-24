<?php

declare(strict_types=1);

use League\Route\RouteGroup;
use Laminas\Diactoros\Response;
use League\Route\Strategy\ApplicationStrategy;

router()->group('/', function (RouteGroup $route) {
    $route->get('/', function ($request) {
        $response = new Response;
        $response->getBody()->write(app()->render('test'));
        return $response->withStatus(200);
    });
})->setStrategy(new ApplicationStrategy);
