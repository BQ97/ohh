<?php

declare(strict_types=1);

namespace App\Router;

use App\Request;
use App\File\Loader;
use League\Route\{
    RouteGroup,
    Router as BaseRouter,
    Strategy\JsonStrategy,
    Strategy\ApplicationStrategy
};
use App\Router\middleware\{
    Exception,
    web\Base as WebMiddleware,
    json\Base as JsonMiddleware
};
use Laminas\Diactoros\ResponseFactory;
use Laminas\HttpHandlerRunner\Emitter\SapiEmitter;

class Router
{
    use \App\Router\traits\ExceptionTrait;

    /**
     * @var \App\Application 
     */
    private $container;

    /**
     * @var \League\Route\Router
     */
    private $handler;

    /**
     * @var array $middlewares
     */
    protected $middlewares = [
        Exception::class,
        WebMiddleware::class,
        JsonMiddleware::class,
    ];

    public function __construct(\App\Application $app)
    {
        $this->container = $app;

        $this->initEnv();

        $this->handler = new BaseRouter();

        $this->middlewares();

        $this->registerRoutes();
    }

    /**
     * 判断env配置
     */
    private function initEnv()
    {
        if ($this->container->env->get('APP_ENV', 'dev') === 'dev') {
            // 在本地如果没有打开虚拟主机，就需要把 PATH_INFO 的值 付给 REQUEST_URI
            isset($_SERVER['PATH_INFO']) && $_SERVER['REQUEST_URI'] = $_SERVER['PATH_INFO'];
        }
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
                'strategy' => new ApplicationStrategy
            ],
            [
                'group' => '/api',
                'file' => 'api.php',
                'strategy' => new JsonStrategy(new ResponseFactory(), JSON_UNESCAPED_UNICODE)
            ]
        ];

        foreach ($routes as $item) {
            $item['strategy']->setContainer($this->container);

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
            array_reduce(Loader::loadFile(ROUTE_PATH . $file), function (RouteGroup $route, array $params) {
                call_user_func_array([$route, 'map'], $params);
                return $route;
            }, $route);
        };
    }

    public function send()
    {
        $serverRequest = Request::createServerRequest();

        try {

            $response = $this->handler()->handle($serverRequest);
        } catch (\Throwable $th) {

            $response = $this->getExceptionResponse($th, $serverRequest);
        } finally {

            $sapiEmitter = new SapiEmitter;

            return $sapiEmitter->emit($response);
        }
    }

    public function __call($name, $arguments)
    {
        return call_user_func_array([$this->handler(), $name], $arguments);
    }
}
