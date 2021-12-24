<?php

declare(strict_types=1);

namespace App\Router;

use App\Request;
use League\Route\Router as BaseRouter;
use App\Router\middleware\web\Base as WebMiddleware;
use App\Router\middleware\json\Base as JsonMiddleware;
use Laminas\HttpHandlerRunner\Emitter\SapiEmitter;
use League\Route\RouteGroup;
use Laminas\Diactoros\ResponseFactory;
use League\Route\Strategy\JsonStrategy;
use League\Route\Strategy\ApplicationStrategy;

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

        $this->registerWebRoute();

        $this->registerApiRoute();
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
    public function middlewares()
    {
        return array_reduce($this->middlewares, fn (BaseRouter $router, String $middleware) => $router->middleware(new $middleware), $this->handler);
    }

    private function registerWebRoute()
    {
        $this->handler()->group('/', function (RouteGroup $route) {

            $webRoutes = require_once ROUTE_PATH . 'web.php';

            foreach ($webRoutes as $item) {
                $route->map(...$item);
            }
        })->setStrategy(new ApplicationStrategy);
    }

    private function registerApiRoute()
    {
        $this->handler()->group('/api', function (RouteGroup $route) {
            $apiRoutes = require_once ROUTE_PATH . 'api.php';

            foreach ($apiRoutes as $item) {
                $route->map(...$item);
            }
        })->setStrategy(new JsonStrategy(new ResponseFactory(), JSON_UNESCAPED_UNICODE));
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
