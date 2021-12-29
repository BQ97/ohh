<?php

declare(strict_types=1);

namespace App\Router\middleware\json;

use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Server\RequestHandlerInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Message\ResponseInterface;
use Laminas\Diactoros\Response\JsonResponse;
use League\Route\Http\Exception\BadRequestException;
use League\Route\Http\Exception\UnauthorizedException;
use League\Route\Http\Exception\ForbiddenException;
use League\Route\Http\Exception\NotFoundException;
use League\Route\Http\Exception\MethodNotAllowedException;
use League\Route\Http\Exception\NotAcceptableException;
use League\Route\Http\Exception\ConflictException;
use League\Route\Http\Exception\GoneException;
use League\Route\Http\Exception\LengthRequiredException;
use League\Route\Http\Exception\PreconditionFailedException;
use League\Route\Http\Exception\UnsupportedMediaException;
use League\Route\Http\Exception\ExpectationFailedException;
use League\Route\Http\Exception\ImATeapotException;
use League\Route\Http\Exception\PreconditionRequiredException;
use League\Route\Http\Exception\TooManyRequestsException;
use League\Route\Http\Exception\UnavailableForLegalReasonsException;

class Base implements MiddlewareInterface
{
    /**
     * @see https://route.thephpleague.com/5.x/strategies/
     * @var array 异常列表
     */
    protected $exceptions = [
        400 => BadRequestException::class,
        401 => UnauthorizedException::class,
        403 => ForbiddenException::class,
        404 => NotFoundException::class,
        405 => MethodNotAllowedException::class,
        406 => NotAcceptableException::class,
        409 => ConflictException::class,
        410 => GoneException::class,
        411 => LengthRequiredException::class,
        412 => PreconditionFailedException::class,
        415 => UnsupportedMediaException::class,
        417 => ExpectationFailedException::class,
        418 => ImATeapotException::class,
        428 => PreconditionRequiredException::class,
        429 => TooManyRequestsException::class,
        451 => UnavailableForLegalReasonsException::class,
    ];

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

                    if (empty($this->exceptions[$data[0]])) {
                        $jsonData = ['ret' => $data[0], 'data' => [], 'msg' => $data[1]];
                    } else {
                        throw new $this->exceptions[$data[0]]($data[1]);
                    }
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
