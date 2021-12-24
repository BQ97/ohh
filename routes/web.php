<?php

declare(strict_types=1);

return [
    [
        'GET', '/', fn ($request) => view('test')
    ],
    [
        'GET', '/test', '\\modules\\Index::test'
    ],
];
