<?php

declare(strict_types=1);

namespace App\Router\middleware;

use App\Router\traits\ExceptionTrait;
use Psr\Http\Server\{MiddlewareInterface, RequestHandlerInterface};
use Psr\Http\Message\{ServerRequestInterface, ResponseInterface};

class Exception implements MiddlewareInterface
{
    use ExceptionTrait;

    public function process(ServerRequestInterface $request, RequestHandlerInterface $handler): ResponseInterface
    {
        try {
            $response = $handler->handle($request);

            return $response;
        } catch (\Throwable $th) {

            return $this->getExceptionResponse($th, $request);
        }
    }
}
