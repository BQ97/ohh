<?php

declare(strict_types=1);

use League\Route\RouteGroup;

router()->jsonStrategy()->group('/api', function (RouteGroup $route) {
    $route->get('/', '\\modules\\Index::indexAction');

    $route->get('/index', '\\modules\\Index::indexAction');

    $route->get('/test', '\\modules\Index::testAction');

    $route->get('/{module}/{class}/{action}', function ($request, $args) {
        $module = $args['module'];
        $class = ucfirst($args['class']);
        $action = $args['action'];

        $class = "\\modules\\{$module}\\{$class}";

        if (!class_exists($class)) {
            return [404, 'not found'];
        }

        if (!method_exists($class, $action)) {
            return [404, 'not found'];
        }

        return app($class)->$action();
    });
});
