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
        return in_array($id, ['snowflake', Snowflake::class]);
    }

    public function register(): void
    {
        $SnowFlake = new Snowflake();
        $SnowFlake->setStartTimeStamp(strtotime('2020-01-01 08:00:00'));
        $SnowFlake->setSequenceResolver(new RandomSequenceResolver());

        $this->getContainer()->add(Snowflake::class, $SnowFlake)->setAlias('snowflake');
    }
}
