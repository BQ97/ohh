<?php

declare(strict_types=1);

namespace App\Providers;

use App\Console;
use League\Container\ServiceProvider\AbstractServiceProvider;

class ConsoleProvider extends AbstractServiceProvider
{
    public function provides(string $id): bool
    {
        return in_array($id, ['console', Console::class]);
    }

    public function register(): void
    {
        $this->getContainer()->add(Console::class)->setAlias('console')->addArgument(app());
    }
}
