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
        $this->getContainer()->add(Router::class)->setAlias('router')->addArgument($this->getContainer());
    }
}
