<?php

declare(strict_types=1);

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

        if (in_array('application/json', $response->getHeader('content-type'), true)) {
            $stream = $response->getBody()->__toString();

            $data = json_decode($stream, true);

            $jsonData = [];

            if (isset($data[0]) && is_int($data[0]) && isset($data[1])) {
                if ($data[0] === 200) {
                    $jsonData = ['ret' => 200, 'data' => $data[1], 'msg' => $data[3] ?? ''];
                } else {
                    $jsonData = ['ret' => $data[0], 'data' => [], 'msg' => $data[1]];
                }
            } else {
                $jsonData = ['ret' => 500, 'data' => [], 'msg' => '数据格式错误'];
            }

            return new JsonResponse($jsonData, $response->getStatusCode(), $response->getHeaders(), JSON_UNESCAPED_UNICODE);
        } else {
            return $response;
        }
    }
}
