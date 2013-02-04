<?php

use Monolog\Logger;
use Monolog\Handler\StreamHandler;
use Monolog\Handler\FingersCrossedHandler;

service('logger', function() {
    $logger = new Logger('default');
    $logger->pushHandler(new FingersCrossedHandler(
        new StreamHandler(APP_DIR . '/tmp/logs/app.log', Logger::ERROR)
    ));
    return $logger;
});

