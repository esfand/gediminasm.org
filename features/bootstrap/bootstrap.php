<?php

define('APP_ENV', 'testing');
define('APP_DIR', __DIR__ . '/../../');

require_once 'PHPUnit/Autoload.php';
require_once 'PHPUnit/Framework/Assert/Functions.php';

// load framework
require APP_DIR . '/framework.php';

// load services required for tests
require APP_DIR . '/services/db.php';
require APP_DIR . '/services/logger.php';

