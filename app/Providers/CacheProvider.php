<?php

declare(strict_types=1);

namespace App\Providers;

use League\Container\ServiceProvider\AbstractServiceProvider;
use Symfony\Component\Cache\Psr16Cache;

class CacheProvider extends AbstractServiceProvider
{
    public function provides(string $id): bool
    {
        return in_array($id, ['cache', Psr16Cache::class]);
    }

    public function register(): void
    {
        $env = $this->getContainer()->get('env');

        $namespace = $env->get('CACHE_PREFIX', 'BoQing');
        $defaultLifetime = (int)$env->get('CACHE_TTL', 0);

        $cacheAdapter = match ($env->get('CACHE_STORE')) {
            'pdo',
            'database' => new \Symfony\Component\Cache\Adapter\PdoAdapter(
                $this->getContainer()->get('db')->pdo,
                $namespace,
                $defaultLifetime
            ),
            'file' => new \Symfony\Component\Cache\Adapter\FilesystemAdapter(
                $namespace,
                $defaultLifetime,
                CACHE_PATH
            ),
            default => new \Symfony\Component\Cache\Adapter\PhpFilesAdapter(
                $namespace,
                $defaultLifetime,
                CACHE_PATH
            ),
        };

        $this->getContainer()->add(Psr16Cache::class)->addArgument($cacheAdapter)->setAlias('cache');
    }
}
