<?php

declare(strict_types=1);

namespace App\Router;

use App\Request;
use League\Route\Strategy\ApplicationStrategy;
use League\Route\Router as BaseRouter;
use App\Router\middleware\web\Base as WebMiddleware;
use App\Router\middleware\json\Base as JsonMiddleware;
use Laminas\HttpHandlerRunner\Emitter\SapiEmitter;
use Laminas\Diactoros\ResponseFactory;
use League\Route\Strategy\JsonStrategy;

class Router
{
    /**
     * @var \League\Route\Router
     */
    private $handler;

    /**
     * @var array $middlewares
     */
    protected $middlewares = [
        WebMiddleware::class,
        JsonMiddleware::class,
    ];

    public function __construct()
    {
        $this->handler = new BaseRouter();

        $this->middlewares();
    }

    /**
     * @return \League\Route\Router
     */
    public function handler()
    {
        return $this->handler;
    }

    /**
     * @return \League\Route\Router
     */
    public function applicationStrategy()
    {
        $this->handler->setStrategy(new ApplicationStrategy);

        return $this->handler;
    }

    /**
     * @return \League\Route\Router
     */
    public function jsonStrategy()
    {
        $responseFactory = new ResponseFactory();

        $strategy = new JsonStrategy($responseFactory, JSON_UNESCAPED_UNICODE);

        $this->handler->setStrategy($strategy);

        return $this->handler;
    }

    /**
     * @return \League\Route\Router
     */
    public function middlewares()
    {
        return array_reduce($this->middlewares, fn (BaseRouter $router, String $middleware) => $router->middleware(new $middleware), $this->handler);
    }

    public function send()
    {
        $response = $this->handler()->handle(Request::createServerRequest());

        $sapiEmitter = new SapiEmitter;

        return $sapiEmitter->emit($response);
    }

    public function __call($name, $arguments)
    {
        return call_user_func_array([$this->handler(), $name], $arguments);
    }
}
