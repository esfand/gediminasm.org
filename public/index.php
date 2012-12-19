<?php
// environment: production, development or testing usually
// nginx virtual host entry example "fastcgi_param APP_ENV development;"
define('APP_ENV', isset($_SERVER['APP_ENV']) ? $_SERVER['APP_ENV'] : 'production');
define('APP_DIR', realpath(__DIR__ . '/../'));

// Note: there won't be any stupid misstake prevention checks like invalid file paths,
// these errors will be clearly visible
include APP_DIR.'/functor.php';

// define error handling before any controller actions
include APP_DIR.'/error_handling.php';

// load services
foreach (glob(APP_DIR.'/services/*.php') as $service) {
    include $service;
}

// load controllers
foreach (glob(APP_DIR.'/controllers/*.php') as $controller) {
    include $controller;
}

dispatch(); // process the request

