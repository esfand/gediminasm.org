<?php

define('APP_DIR', realpath(__DIR__ . '/../../'));

include APP_DIR.'/framework.php';

foreach (glob(APP_DIR.'/services/*.php') as $service) {
    include $service;
}
