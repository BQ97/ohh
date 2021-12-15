<?php

declare(strict_types=1);

use League\Route\Router;
use League\Route\Strategy\ApplicationStrategy;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Laminas\Diactoros\ServerRequestFactory;
use Laminas\Diactoros\ResponseFactory;
use League\Route\Strategy\JsonStrategy;
use Laminas\HttpHandlerRunner\Emitter\SapiEmitter;

/**
 * @var \App\Application
 */
$app = require_once '../main.php';

$request = $app->request->createServerRequest();

$responseFactory = new ResponseFactory();

$strategy = new JsonStrategy($responseFactory);

$router = new Router();

$router->setStrategy($strategy);

$router->get('/test', function (ServerRequestInterface $request) : array {
    return [
        'ret' => 200,
        'data' => [

            'test' => '123'
        ],
        'msg' => 'ok'
    ];
});

$response = $router->dispatch($request);


$sapiEmitter = new SapiEmitter;

$sapiEmitter->emit($response);


exit;

$pathInfo = $app->request->server('PATH_INFO', $app->request->server('argv.1'));

if ($pathInfo) {

    $paths = explode('/', $pathInfo);

    $paths = array_filter(array_reverse($paths));

    if (count($paths) > 1) {
        $action = $paths[0] . 'Action';
        unset($paths[0]);
        $paths[1] = ucfirst($paths[1]);
        $class = '\\modules\\' . join('\\', array_reverse($paths));
    } else {
        $class = '\\modules\\Index';
        $action = $paths[0] . 'Action';
    }

    header('Content-Type:application/json');

    echo json_encode($app->make($class)->$action(), JSON_UNESCAPED_UNICODE);
} else {
    echo json_encode($app->make('\\modules\\Index')->indexAction(), JSON_UNESCAPED_UNICODE);
}
