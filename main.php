<?php

declare(strict_types=1);

/**
 * @var string APP_PATH 项目目录
 */
const APP_PATH = __DIR__;

/**
 * @var string DS 系统分隔符 等价于 DIRECTORY_SEPARATOR
 */
const DS = DIRECTORY_SEPARATOR;

/**
 * @var string VIEW_PATH 视图目录
 */
const VIEW_PATH = APP_PATH . DS . 'views' . DS;

/**
 * @var string ENV_PATH ENV配置目录
 */
const ENV_PATH = APP_PATH . DS . '.env';

/**
 * @var string CACHE_PATH 缓存目录
 */
const CACHE_PATH = APP_PATH . DS . 'cache' . DS;

/**
 * @var string UPLOAD_PATH
 */
const UPLOAD_PATH = APP_PATH . DS . 'uploads' . DS;

require APP_PATH . DS . 'vendor' . DS . 'autoload.php';

/**
 * @var \App\Application
 */
$app = \App\Application::getInstance();

return $app;
