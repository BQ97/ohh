<?php

declare(strict_types=1);

return [
    [
        'GET', '/', '\\modules\\Index::indexAction',
    ],
    [
        'GET', '/index', '\\modules\\Index::indexAction'
    ],
    [
        'GET', '/test', '\\modules\Index::testAction'
    ],
    [
        'POST', '/mail', '\\modules\Index::mailAction'
    ],

    [
        'POST', '/finance/create-excel', '\\modules\\Finance::createExcel'
    ],

    [
        '*', '/{module}/{class}/{action}', function ($request, $args) {
            $module = $args['module'];
            $class = ucfirst($args['class']);
            $action = $args['action'];

            $class = "\\modules\\{$module}\\{$class}";

            if (!class_exists($class)) {
                return [404, 'not found' . $class];
            }

            if (!method_exists($class, $action)) {
                return [404, 'not found' . $class . '::' . $action];
            }

            return app($class)->$action();
        }
    ]
];
