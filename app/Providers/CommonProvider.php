<?php

declare(strict_types=1);


namespace App\Providers;

use App\Env;
use App\File\Zip;
use GuzzleHttp\Client;
use League\Container\ServiceProvider\AbstractServiceProvider;
use League\Plates\Engine;
use Psy\Shell;

class CommonProvider extends AbstractServiceProvider
{
    /**
     * @var string[] 服务提供者
     */
    private const SERVICE_CLASS = [
        'env' => Env::class,
        'httpClient' => Client::class,
        'templates' => Engine::class,
        'shell' => Shell::class,
        'zip' => Zip::class,
    ];

    public function provides(string $id): bool
    {
        return in_array($id, [...array_keys(static::SERVICE_CLASS), ...array_values(static::SERVICE_CLASS)]);
    }

    public function register(): void
    {
        foreach (static::SERVICE_CLASS as $alias => $class) {

            $this->getContainer()->add($class);

            $object = $this->getContainer()->get($class);

            $this->getContainer()->add($alias, $object);
        }
    }
}
