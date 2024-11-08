<?php

declare(strict_types=1);

/**
 * @var string DS 系统分隔符 等价于 DIRECTORY_SEPARATOR
 */
const DS = DIRECTORY_SEPARATOR;

/**
 * @var string APP_PATH 项目目录
 */
const APP_PATH = __DIR__ . DS;

/**
 * @var string VIEW_PATH 视图目录
 */
const VIEW_PATH = APP_PATH . 'views' . DS;

/**
 * @var string MODULE_PATH 各个模块目录
 */
const MODULE_PATH = APP_PATH . 'modules' . DS;

/**
 * @var string ENV_PATH ENV配置目录
 */
const ENV_PATH = APP_PATH . '.env';

/**
 * @var string CACHE_PATH 缓存目录
 */
const CACHE_PATH = APP_PATH . 'cache' . DS;

/**
 * @var string UPLOAD_PATH
 */
const UPLOAD_PATH = APP_PATH . 'uploads' . DS;

/**
 * @var string STATTIC_PATH
 */
const STATIC_PATH = APP_PATH . 'static' . DS;

/**
 * @var string ROUTE_PATH
 */
const ROUTE_PATH = APP_PATH . 'routes' . DS;

/**
 * @var string ROUTE_PATH
 */
const CONFIG_PATH = APP_PATH . 'config' . DS;

/**
 * @var string PUBLIC_PATH
 */
const PUBLIC_PATH = APP_PATH . 'public' . DS;

/**
 * @var string LOG_PATH
 */
const LOG_PATH = APP_PATH . 'logs' . DS;

require APP_PATH . 'vendor' . DS . 'autoload.php';

/**
 * @var \App\Application
 */
$app = \App\Application::getInstance();

return $app;
