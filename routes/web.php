<?php

declare(strict_types=1);

use Laminas\Diactoros\Response;

router()->applicationStrategy()->get('/', function ($request) {
    $response = new Response;
    $response->getBody()->write(app()->render('test'));
    return $response->withStatus(200);
});