<?php

declare(strict_types=1);

namespace App\Providers;

use League\Container\ServiceProvider\AbstractServiceProvider;
use League\Plates\Engine;

class TemplateProvider extends AbstractServiceProvider
{
    public function provides(string $id): bool
    {
        return in_array($id, ['templates', Engine::class]);
    }

    public function register(): void
    {
        $this->getContainer()->add(Engine::class)->setAlias('templates')->addArguments([VIEW_PATH, 'phtml']);
    }
}
