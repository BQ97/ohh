<?php

declare(strict_types=1);

namespace App\Providers;

use App\Router\Router;
use League\Container\ServiceProvider\AbstractServiceProvider;

class RouterProvider extends AbstractServiceProvider
{
    public function provides(string $id): bool
    {
        return in_array($id, ['router', Router::class]);
    }

    public function register(): void
    {
        $container = $this->getContainer();

        $container->add(Router::class)->addArgument($container)->setAlias('router');
    }
}
