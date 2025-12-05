<?php

declare(strict_types=1);

namespace App;

use App\Providers\CommonProvider;
use App\Providers\DbProvider;
use App\Providers\FakerProvider;
use App\Providers\RouterProvider;
use App\Providers\SnowFlakeProvider;
use App\Providers\ConsoleProvider;
use League\Container\Container;
use League\Container\ReflectionContainer;
use Psr\Container\ContainerInterface;
use App\Providers\MailProvider;
use App\Providers\OpenAiProvider;

/**
 * @name 容器
 * @property \App\File\Zip                      $zip
 * @property \App\Env                           $env
 * @property \Psy\Shell                         $shell
 * @property \GuzzleHttp\Client                 $httpClient
 * @property \Medoo\Medoo                       $db
 * @property \App\View                          $view
 * @property \App\Router\Router                 $router
 * @property \Faker\Generator                   $faker
 * @property \Godruoyi\Snowflake\Snowflake      $snow
 * @property \League\CLImate\CLImate            $cli
 * @property \App\Console                       $console
 * @property \OpenAI\Client                     $openai
 * @property \PHPMailer\PHPMailer\PHPMailer     $mail
 */
class Application implements ContainerInterface
{
    public const VERSION = '2.11.0';

    private Container $container;

    private array $providers = [
        DbProvider::class,
        CommonProvider::class,
        FakerProvider::class,
        RouterProvider::class,
        SnowFlakeProvider::class,
        ConsoleProvider::class,
        OpenAiProvider::class,
        MailProvider::class
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
        $this->container = new Container();

        $this->container->delegate(new ReflectionContainer(true));

        $this->container->defaultToShared();

        $this->registerProviders();

        static::$instance = $this;
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
            'version' => self::VERSION,
            'PHP_VERSION_ID' => PHP_VERSION_ID,
            'date' => date('Y-m-d H:i:s'),
        ];
    }
}
