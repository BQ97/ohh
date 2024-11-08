<?php

declare(strict_types=1);

namespace App\Providers;

use League\Container\ServiceProvider\AbstractServiceProvider;
use Faker\Factory;
use Faker\Generator;

class FakerProvider extends AbstractServiceProvider
{
    public function provides(string $id): bool
    {
        return in_array($id, ['faker', Generator::class]);
    }

    public function register(): void
    {
        $this->getContainer()->add(Generator::class, Factory::create('zh_CN'))->setAlias('faker');
    }
}
