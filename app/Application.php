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
 */
class Application implements ContainerInterface
{
    private Container $container;

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
