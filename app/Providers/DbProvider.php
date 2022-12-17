<?php

declare(strict_types=1);

namespace App\Providers;

use League\Container\ServiceProvider\AbstractServiceProvider;
use Medoo\Medoo;
use PDO;

class DbProvider extends AbstractServiceProvider
{
    public function provides(string $id): bool
    {
        return in_array($id, ['db', Medoo::class]);
    }

    public function register(): void
    {
        $env = $this->getContainer()->get('env');

        $this->getContainer()->add(Medoo::class)->addArgument([
            'type'      => $env->get('DB_CONNECTION', 'mysql'),
            'database'  => $env->get('DB_DATABASE', ''),
            'host'      => $env->get('DB_HOST', 'localhost'),
            'charset'   => 'utf8mb4',
            'collation' => 'utf8mb4_general_ci',
            'port'      => $env->get('DB_PORT', 3306),
            'prefix'    => $env->get('DB_PRIFIX', ''),
            'username'  => $env->get('DB_USERNAME', 'root'),
            'password'  => $env->get('DB_PASSWORD', ''),
            'option'    => [
                PDO::ATTR_STRINGIFY_FETCHES => false,
                PDO::ATTR_EMULATE_PREPARES => false
            ],
            'logging' => true
        ])->setAlias('db');
    }
}
