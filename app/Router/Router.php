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
use App\Router\Strategy\WebStrategy;

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

        $this->registerRoutes();
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

    private function registerRoutes()
    {
        $routes = [
            [
                'group' => '/',
                'file' => 'web.php',
                'strategy' => new WebStrategy
            ],
            [
                'group' => '/api',
                'file' => 'api.php',
                'strategy' => new JsonStrategy(new ResponseFactory(), JSON_UNESCAPED_UNICODE)
            ]
        ];

        $container = app();

        foreach ($routes as $item) {
            $item['strategy']->setContainer($container);

            $this->handler()->group($item['group'], $this->loadRouteFile($item['file']))->setStrategy($item['strategy']);
        }
    }

    /**
     * @param string
     * @return \Closure
     */
    private function loadRouteFile(string $file)
    {
        return function (RouteGroup $route) use ($file) {
            array_reduce(requireFile(ROUTE_PATH . $file), function (RouteGroup $route, array $params) {
                $route->map(...$params);
                return $route;
            }, $route);
        };
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
