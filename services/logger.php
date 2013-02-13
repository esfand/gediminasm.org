<?php

use Monolog\Logger;
use Monolog\Handler\StreamHandler;
use Monolog\Handler\FingersCrossedHandler;

service('logger', function(array $config) {
    $logger = new Logger('default');
    $logger->pushHandler(new FingersCrossedHandler(
        new StreamHandler(APP_DIR . '/tmp/logs/app.log', Logger::DEBUG)
    ), Logger::ERROR);
    return $logger;
});

