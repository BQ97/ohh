<?php

declare(strict_types=1);

use App\Router\Web;
use Laminas\Diactoros\Response;

Web::get('/', function ($request) {
    $response = new Response;
    $response->getBody()->write(app()->render('test'));
    return $response->withStatus(200);
});
