<?php

declare(strict_types=1);

namespace App;

use Faker\Factory;
use PDO;
use App\Logger;

/**
 * Class Application.
 */
class Application extends Container
{
    /**
     * 初始化应用
     */
    public function __construct()
    {
        $this->initLogger();

        $this->initDb();

        $this->initFaker();

        $this->initTemplate();
    }

    /**
     * 启动数据库
     */
    private function initDb()
    {
        $this->make('db', [[
            'type'      => $this->env->get('DB_CONNECTION', 'mysql'),
            'database'  => $this->env->get('DB_DATABASE', ''),
            'host'      => $this->env->get('DB_HOST', 'localhost'),
            'charset'   => 'utf8mb4',
            'collation' => 'utf8mb4_general_ci',
            'port'      => $this->env->get('DB_PORT', 3306),
            'prefix'    => $this->env->get('DB_PRIFIX', ''),
            'username'  => $this->env->get('DB_USERNAME', 'root'),
            'password'  => $this->env->get('DB_PASSWORD', ''),
            'option'    => [
                PDO::ATTR_STRINGIFY_FETCHES => false,
                PDO::ATTR_EMULATE_PREPARES => false
            ],
            'logging' => true
        ]]);
    }

    /**
     * 启动Faker
     */
    private function initFaker()
    {
        $this->bindTo('faker', Factory::create('zh_CN'));
    }

    /**
     * 启动 Logger
     */
    private function initLogger()
    {
        Logger::setBasePath(LOG_PATH);
    }

    /**
     * 启动模板引擎
     */
    private function initTemplate()
    {
        $this->templates->setDirectory(VIEW_PATH)->setFileExtension('phtml');
    }

    /**
     * 获取model
     * @param string $name 表名
     * @param string $pk  主键
     *
     * @return \App\Model
     */
    public function model(String $name, String $pk = 'id'): \App\Model
    {
        return $this->model->setTable($name)->setPk($pk);
    }

    /**
     * Create a new template and render it.
     * @param  string $name
     * @param  array  $data
     * @param  bool  $return
     * @return string
     */
    public function render($name, array $data = []): string
    {
        if ($this->templates->exists($name)) {

            return $this->templates->render($name, $data);
        }

        return $this->templates->render('not-found', ['name' => $name]);
    }

    public function __debugInfo()
    {
        return [
            'app_name' => $this->env->get('APP_NAME'),
            'version' => '1.0.0',
            'PHP_VERSION_ID' => PHP_VERSION_ID,
            'date' => date('Y-m-d H:i:s'),
            'container' => parent::__debugInfo(),
        ];
    }
}
