<?php

include __DIR__.'/../functor.php';

define('DEBUG', isset($_SERVER['DEBUG'])); // nginx fastcgi_param DEBUG 1; as an example

// load services
foreach (glob(__DIR__.'/../services/*.php') as $service) {
    include $service;
}

// define error handling before any controller actions
include __DIR__.'/../error_handling.php';

// load controllers
foreach (glob(__DIR__.'/../controllers/*.php') as $controller) {
    include $controller;
}

dispatch(); // process the request

