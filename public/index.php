<?php

declare(strict_types=1);

/**
 * @var \App\Application
 */
$app = require_once '../main.php';

// 在本地如果没有打开虚拟主机，就需要把 PATH_INFO 的值 付给 REQUEST_URI
// $_SERVER['REQUEST_URI'] = $_SERVER['PATH_INFO'];

require_once '../routes/web.php';
require_once '../routes/api.php';

router()->send();
