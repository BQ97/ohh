<?php

declare(strict_types=1);

namespace App;

use App\Providers\CommonProvider;
use App\Providers\DbProvider;
use App\Providers\FakerProvider;
use App\Providers\RouterProvider;
use App\Providers\SnowFlakeProvider;
use League\Container\Container;
use League\Container\ReflectionContainer;
use Psr\Container\ContainerInterface;
use App\File\Loader;

/**
 * @name 容器
 * @property \App\File\Zip                      $zip
 * @property \App\Env                           $env
 * @property \Psy\Shell                         $shell
 * @property \GuzzleHttp\Client                 $httpClient
 * @property \Medoo\Medoo                       $db
 * @property \League\Plates\Engine              $templates
 * @property \App\Router\Router                 $router
 * @property \Faker\Generator                   $faker
 * @property \Godruoyi\Snowflake\Snowflake      $snowflake
 * @property \League\CLImate\CLImate             $cli
 */
class Application implements ContainerInterface
{
    private Container $container;

    private array $commands = [
        'list' => \modules\Consoles\ListConsole::class,
        'create-manager-db' => \modules\Consoles\CreateManagerDb::class,
        'create-member-db' => \modules\Consoles\CreateMemberDb::class,
        'create-shell' => \modules\Consoles\CreateShell::class,
    ];

    private array $providers = [
        DbProvider::class,
        CommonProvider::class,
        FakerProvider::class,
        RouterProvider::class,
        SnowFlakeProvider::class
    ];

    private static $instance;

    public static function getInstance()
    {
        if (!(static::$instance instanceof static)) {
            static::$instance = new static();
        }

        return static::$instance;
    }

    public function __construct()
    {
        // 启动 Logger
        Logger::setBasePath(LOG_PATH);

        $this->container = new Container();

        $this->container->delegate(new ReflectionContainer(true));

        $this->container->defaultToShared();

        $this->registerProviders();

        // 启动模板引擎
        $this->templates->setDirectory(VIEW_PATH)->setFileExtension('phtml');

        static::$instance = $this;
    }

    public function getCommands()
    {
        return $this->commands;
    }

    public function runCmd()
    {
        if (preg_match("/cli/i", php_sapi_name())) {
            $args = $_SERVER['argv'];

            if (count($args) < 2) {
                $this->cli->error('[ERROR]请输入您要执行的命令');
                return false;
            }

            $command = $args[1];

            if (empty($this->commands[$command])) {
                $this->cli->error('[ERROR]该命令不存在');
                return false;
            }

            $class = $this->commands[$command];

            $consoleObject = new $class($this);

            return call_user_func([$consoleObject, 'run']);
        }

        throw new \Exception('this method must be cli mode');
    }

    /**
     * Create a new template and render it.
     * @param  string $name
     * @param  array  $data
     * @param  bool  $return
     * @return string
     */
    public function render(string $name, array $data = []): string
    {
        if ($this->templates->exists($name)) {
            return $this->templates->render($name, $data);
        }

        return $this->templates->render('not-found', ['name' => $name]);
    }

    public function getConfig(string $filename)
    {
        $filename = CONFIG_PATH . $filename . '.php';

        if (file_exists($filename)) {
            return Loader::loadFile($filename);
        }

        return null;
    }

    private function registerProviders()
    {
        return array_reduce($this->providers, fn (Container $container, string $class) => $container->addServiceProvider(new $class), $this->container);
    }

    public function has(string $id): bool
    {
        return $this->container->has($id);
    }

    public function get(string $id)
    {
        if (in_array($id, ['app', static::class])) {
            return $this;
        }

        return $this->container->get($id);
    }

    public function __get($name)
    {
        return $this->get($name);
    }

    public function __isset($name)
    {
        return $this->has($name);
    }

    public function __debugInfo()
    {
        return [
            'app_name' => $this->env->get('APP_NAME'),
            'version' => '2.0.1',
            'PHP_VERSION_ID' => PHP_VERSION_ID,
            'date' => date('Y-m-d H:i:s'),
        ];
    }
}
