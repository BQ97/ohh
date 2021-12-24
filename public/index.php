<?php

declare(strict_types=1);

/**
 * @var \App\Application
 */
$app = require_once '../main.php';

require_once '../routes/web.php';
require_once '../routes/api.php';

router()->send();
