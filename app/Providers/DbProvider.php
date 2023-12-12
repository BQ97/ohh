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

        $config = [
            'type'      => $env->get('DB_CONNECTION', 'mysql'),
            'database'  => $env->get('DB_DATABASE', ''),
            'host'      => $env->get('DB_HOST', 'localhost'),
            'port'      => $env->get('DB_PORT', 3306),
            'prefix'    => $env->get('DB_PRIFIX', ''),
            'username'  => $env->get('DB_USERNAME', 'root'),
            'password'  => $env->get('DB_PASSWORD', ''),
            'option'    => [
                PDO::ATTR_STRINGIFY_FETCHES => false,
                PDO::ATTR_EMULATE_PREPARES => false
            ],
            'logging' => true
        ];

        if ($config['type'] === 'mysql') {
            $config['charset'] = 'utf8mb4';
            $config['collation'] = 'utf8mb4_general_ci';
        } else {
            $config['charset'] = 'utf8';
        }

        $this->getContainer()->add(Medoo::class)->addArgument($config)->setAlias('db');
    }
}
