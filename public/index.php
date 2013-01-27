<?php
// environment: production, development or testing as usual
// nginx virtual host entry example "fastcgi_param APP_ENV development;"
define('APP_ENV', isset($_SERVER['APP_ENV']) ? $_SERVER['APP_ENV'] : 'development');
define('APP_DIR', realpath(__DIR__ . '/../'));

// Note: there won't be any stupid mistake prevention checks like invalid file paths,
// these errors will be clearly visible by php error/exception handler
include APP_DIR.'/functor.php';

// define error handling before any controller actions
include APP_DIR.'/error_handling.php';

// load services. if there is a bunch of services, recursive reading can be done
foreach (glob(APP_DIR.'/services/*.php') as $service) {
    include $service;
}

// load controllers
foreach (glob(APP_DIR.'/controllers/*.php') as $controller) {
    include $controller;
}

// run without any argument
echo dispatch(); // process the request and output results

