<?php

declare(strict_types=1);

namespace App\Router\middleware\json;

use Psr\Http\Server\{MiddlewareInterface, RequestHandlerInterface};
use Psr\Http\Message\{ServerRequestInterface, ResponseInterface};
use Laminas\Diactoros\Response\JsonResponse;

class Base implements MiddlewareInterface
{
    /**
     * @see https://route.thephpleague.com/5.x/strategies/
     * @var array 异常列表
     */
    protected array $exceptions = [
        400 => \League\Route\Http\Exception\BadRequestException::class,
        401 => \League\Route\Http\Exception\UnauthorizedException::class,
        403 => \League\Route\Http\Exception\ForbiddenException::class,
        404 => \League\Route\Http\Exception\NotFoundException::class,
        405 => \League\Route\Http\Exception\MethodNotAllowedException::class,
        406 => \League\Route\Http\Exception\NotAcceptableException::class,
        409 => \League\Route\Http\Exception\ConflictException::class,
        410 => \League\Route\Http\Exception\GoneException::class,
        411 => \League\Route\Http\Exception\LengthRequiredException::class,
        412 => \League\Route\Http\Exception\PreconditionFailedException::class,
        415 => \League\Route\Http\Exception\UnsupportedMediaException::class,
        417 => \League\Route\Http\Exception\ExpectationFailedException::class,
        418 => \League\Route\Http\Exception\ImATeapotException::class,
        428 => \League\Route\Http\Exception\PreconditionRequiredException::class,
        429 => \League\Route\Http\Exception\TooManyRequestsException::class,
        451 => \League\Route\Http\Exception\UnavailableForLegalReasonsException::class,
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
