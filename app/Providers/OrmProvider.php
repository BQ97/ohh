<?php

declare(strict_types=1);

namespace App\Providers;

use League\Container\ServiceProvider\AbstractServiceProvider;
use think\DbManager;

class OrmProvider extends AbstractServiceProvider
{
    public function provides(string $id): bool
    {
        return in_array($id, ['orm', DbManager::class]);
    }

    public function register(): void
    {
        $env = $this->getContainer()->get('env');

        $config = [
            // 默认数据连接标识
            'default'   => $env->get('DB_CONNECTION', 'mysql'),

            // 自定义时间查询规则
            'time_query_rule' => [],
            // 自动写入时间戳字段
            // true为自动识别类型 false关闭
            // 字符串则明确指定时间字段类型 支持 int timestamp datetime date
            'auto_timestamp'  => true,

            // 时间字段取出后的默认时间格式
            'datetime_format' => false,

            // 时间字段配置 配置格式：create_time,update_time
            'datetime_field'  => 'create_time,update_time',

            // 数据库连接信息
            'connections' => [
                'pgsql' => [
                    // 数据库类型
                    'type'   => 'pgsql',
                    // 主机地址
                    'hostname' => $env->get('DB_HOST', 'localhost'),
                    'hostport' => $env->get('DB_PORT', 3306),
                    'password' => $env->get('DB_PASSWORD', ''),
                    // 用户名
                    'username' => $env->get('DB_USERNAME', 'root'),
                    // 数据库名
                    'database' => $env->get('DB_DATABASE', ''),
                    // 数据库编码默认采用utf8
                    'charset' => 'utf8',
                    // 数据库表前缀
                    'prefix'  => $env->get('DB_PRIFIX', ''),
                    'params'  => [
                        \PDO::ATTR_STRINGIFY_FETCHES => false,
                        \PDO::ATTR_EMULATE_PREPARES => false
                    ],
                    // 数据库调试模式
                    'break_reconnect'  => true,

                    'fields_strict' => false,
                ],

                'mysql' => [
                    // 数据库类型
                    'type'   => 'mysql',
                    // 主机地址
                    'hostname' => $env->get('DB_HOST', 'localhost'),
                    'hostport' => $env->get('DB_PORT', 3306),
                    'password' => $env->get('DB_PASSWORD', ''),
                    // 用户名
                    'username' => $env->get('DB_USERNAME', 'root'),
                    // 数据库名
                    'database' => $env->get('DB_DATABASE', ''),
                    // 数据库编码默认采用utf8
                    'charset' => 'utf8',
                    // 数据库表前缀
                    'prefix'  => $env->get('DB_PRIFIX', ''),
                    'params'  => [
                        \PDO::ATTR_STRINGIFY_FETCHES => false,
                        \PDO::ATTR_EMULATE_PREPARES => false
                    ],
                    // 数据库调试模式
                    'break_reconnect'  => true,

                    'fields_strict' => false,
                ],
            ],
        ];

        $this->getContainer()->add(DbManager::class, function () use ($config) {
            $orm = new DbManager();
            $orm->setConfig($config);
            return $orm;
        })->setAlias('orm');
    }
}
