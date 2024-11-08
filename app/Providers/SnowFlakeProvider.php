<?php

declare(strict_types=1);

namespace App\Providers;

use League\Container\ServiceProvider\AbstractServiceProvider;
use Godruoyi\Snowflake\Snowflake;
use Godruoyi\Snowflake\RandomSequenceResolver;

class SnowFlakeProvider extends AbstractServiceProvider
{
    public function provides(string $id): bool
    {
        return in_array($id, ['snow', Snowflake::class]);
    }

    public function register(): void
    {
        $this->getContainer()->add(
            Snowflake::class,
            fn () => (new Snowflake())
                ->setStartTimeStamp(strtotime('2024-02-04 09:00:00') * 1000)
                ->setSequenceResolver(new RandomSequenceResolver())
        )->setAlias('snow');
    }
}
