<?php

namespace App;

use Psr\Container\ContainerInterface;
use League\CLImate\CLImate;

class Console
{
    private const HANDLE_METHOD = 'run';

    private ContainerInterface $container;

    private CLImate $cli;

    private array $commands = [
        'list' => \modules\Consoles\ListConsole::class,
        'create-manager-db' => \modules\Consoles\CreateManagerDbConsole::class,
        'create-member-db' => \modules\Consoles\CreateMemberDbConsole::class,
        'create-shell' => \modules\Consoles\CreateShellConsole::class,
        'create-do-project' => \modules\Consoles\CreateDoProjectConsole::class,
        'fanyi' => \modules\Consoles\FanYiConsole::class,
        'keep-alive-ams-cookie' => \modules\Consoles\KeepAliveAmsCookieConsole::class
    ];

    public function __construct(ContainerInterface $container)
    {
        $this->container = $container;
        $this->cli = $container->get('cli');
        $this->cli->arguments->add([
            'action' => [
                'prefix'       => 'a',
                'longPrefix'   => 'action',
                'description'  => "如果传入 application 则执行对应的类方法，例如 \\test\\test::start; \n \t\t如果没有传入，则执行预定义命令",
                'required'     => true,
                'castTo'       => 'string'
            ],
            'args' => [
                'longPrefix'   => 'args',
                'description'  => '执行action的参数,多个参数用“,”分割',
                'required'     => false,
            ],
            'application' => [
                'prefix'       => 'app',
                'longPrefix'   => 'application',
                'description'  => '执行应用下的类方法',
                'required'     => false,
                'noValue'      => true,
            ],
        ]);
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
        return $this->cli->info("[INFO]{$msg}");
    }

    public function error(string $msg)
    {
        return $this->cli->error("[ERROR]{$msg}");
    }

    public function success(string $msg)
    {
        return $this->cli->green("[SUCCESS]{$msg}");
    }

    public function log(string $msg)
    {
        return $this->cli->out($msg);
    }

    public function run()
    {
        if (preg_match("/cli/i", php_sapi_name())) {
            $args = $_SERVER['argv'];

            if (count($args) < 2) {
                return $this->cli->usage();
            }

            if (!$this->cli->arguments->defined('action')) {
                return $this->cli->usage();
            }

            $this->cli->arguments->parse();

            $action = $this->cli->arguments->get('action');

            $arguments = explode(',', $this->cli->arguments->get('args'));

            if ($this->cli->arguments->get('application')) {

                $func = explode('::', $action);

                if (count($func) < 2) {
                    return $this->error('请输入正确的类方法，例如：\\test\\test::start');
                }

                $result = call_user_func_array([$this->container->get($func[0]), $func[1]], $arguments);

                if (is_object($result)) {
                    $input = $this->cli->confirm('该程序返回了一个对象，您可以选择在交互式终端中继续使用它($result)，是否继续？');
                    if ($input->confirmed()) {
                        $this->container->get('shell')->setScopeVariables([
                            'result' => $result
                        ]);
                
                        return $this->container->get('shell')->run();
                    }
                }
                return dump($result);
            }

            $command = $this->getCommand($action);

            if (!$command) {
                return $this->error('该命令不存在');
            }

            return call_user_func_array([new $command($this->container), static::HANDLE_METHOD], $arguments);
        }

        throw new \Exception('this method must be cli mode');
    }
}
