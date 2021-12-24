<?php

declare(strict_types=1);

namespace App\Router;

use App\Request;
use League\Route\Router;
use Laminas\Diactoros\ResponseFactory;
use League\Route\Strategy\JsonStrategy;
use App\Router\middleware\json\Base;
use Laminas\HttpHandlerRunner\Emitter\SapiEmitter;

class Json
{

    /**
     * @var \League\Route\Router
     */
    private static $handler;

    /**
     * @var array $middlewares
     */
    private static $middlewares = [
        Base::class
    ];

    /**
     * @return \League\Route\Router
     */
    public static function handler()
    {
        if (static::$handler instanceof Router) {
            return static::$handler;
        }

        $responseFactory = new ResponseFactory();

        $strategy = new JsonStrategy($responseFactory, JSON_UNESCAPED_UNICODE);

        $router = new Router();

        $router = array_reduce(static::$middlewares, fn (Router $router, String $class) => $router->middleware(new $class), $router);

        static::$handler = $router->setStrategy($strategy);

        return $router;
    }

    public static function send()
    {
        $response = static::handle(Request::createServerRequest());

        $sapiEmitter = new SapiEmitter;

        return $sapiEmitter->emit($response);
    }

    public static function __callStatic($name, $arguments)
    {
        return call_user_func_array([static::handler(), $name], $arguments);
    }

    public function __call($name, $arguments)
    {
        return call_user_func_array([static::handler(), $name], $arguments);
    }
}
