<?php

namespace App;

use Psr\Container\ContainerInterface;

class Console
{
    private const HANDLE_METHOD = 'run';

    private ContainerInterface $container;

    private array $commands = [
        'list' => \modules\Consoles\ListConsole::class,
        'create-manager-db' => \modules\Consoles\CreateManagerDbConsole::class,
        'create-member-db' => \modules\Consoles\CreateMemberDbConsole::class,
        'create-shell' => \modules\Consoles\CreateShellConsole::class,
        'create-do-project' => \modules\Consoles\CreateDoProjectConsole::class,
        'fanyi' => \modules\Consoles\FanYiConsole::class
    ];

    public function __construct(ContainerInterface $container)
    {
        $this->container = $container;
    }

    public function getCommand(string $alias = null)
    {
        if (!$alias) {
            return $this->commands;
        }

        if (empty($this->commands[$alias])) {
            return false;
        }

        return $this->commands[$alias];
    }

    public function info(string $msg)
    {
        return $this->container->get('cli')->info("[INFO]{$msg}");
    }

    public function error(string $msg)
    {
        return $this->container->get('cli')->error("[ERROR]{$msg}");
    }

    public function success(string $msg)
    {
        return $this->container->get('cli')->green("[SUCCESS]{$msg}");
    }

    public function log(string $msg)
    {
        return $this->container->get('cli')->out($msg);
    }

    public function run()
    {
        if (preg_match("/cli/i", php_sapi_name())) {
            $args = $_SERVER['argv'];

            if (count($args) < 2) {
                $this->error('请输入您要执行的命令');
                return false;
            }

            if (count($args) > 2) {
                $result = call_user_func_array([$this->container->get($args[1]), $args[2]], array_slice($args, 3));
                return dump($result);
            }

            $command = $this->getCommand($args[1]);

            if (!$command) {
                $this->error('该命令不存在');
                return false;
            }

            return call_user_func([new $command($this->container), static::HANDLE_METHOD]);
        }

        throw new \Exception('this method must be cli mode');
    }
}
