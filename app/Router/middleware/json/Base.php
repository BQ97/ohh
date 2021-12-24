<?php

namespace App\Router\middleware\json;

use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Server\RequestHandlerInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Message\ResponseInterface;
use Laminas\Diactoros\Response\JsonResponse;

class Base implements MiddlewareInterface
{
    public function process(ServerRequestInterface $request, RequestHandlerInterface $handler): ResponseInterface
    {
        $response = $handler->handle($request);

        $stream = $response->getBody();

        $data = json_decode($stream, true);

        return new JsonResponse([
            'ret' => $data[0],
            'data' => $data[1],
            'msg' => 'ok'
        ], $response->getStatusCode(), $response->getHeaders(), JSON_UNESCAPED_UNICODE);
    }
}
