<?php

include __DIR__.'/../functor.php';

define('DEBUG', isset($_SERVER['DEBUG'])); // nginx fastcgi_param DEBUG 1; as an example

// load services
foreach (glob(__DIR__.'/../services/*.php') as $service) {
    include $service;
}

class JsonException extends Exception {} // used as json error response
// load controllers
foreach (glob(__DIR__.'/../controllers/*.php') as $controller) {
    include $controller;
}

// simple php error handler
set_error_handler(function($level, $message, $file, $line, $context) {
    static $levels = array(
        E_WARNING           => 'Warning',
        E_NOTICE            => 'Notice',
        E_USER_ERROR        => 'User Error',
        E_USER_WARNING      => 'User Warning',
        E_USER_NOTICE       => 'User Notice',
        E_STRICT            => 'Runtime Notice',
        E_RECOVERABLE_ERROR => 'Catchable Fatal Error',
        E_DEPRECATED        => 'Deprecated',
        E_USER_DEPRECATED   => 'User Deprecated',
    );
    if (error_reporting() & $level) {
        // delegate to exception handler
        throw new Exception(sprintf('%s: %s in %s line %d', $levels[$level], $message, $file, $line), 500);
    }
    return false;
});

// exception handler
set_exception_handler(function(Exception $e) {
    // do not use any fancy stuff which might throw another exception or catch it
    // expects status code to be HTTP code
    http_response_code($code = $e->getCode() ?: 500); // create status code header
    $msg = $e->getMessage();
    if (!DEBUG) {
        // respond to user properly
        switch ($code) {
            case 404:
                $msg = "The page you were looking for was not found or recently removed.";
                break;
            default:
                $msg = "The service is currently down.";
        }
    }
    if ($e instanceof JsonException) {
        service('http')->json(array('error' => array(
            'message' => $msg,
            'code' => $code
        )));
    } else {
        echo $msg;
    }
});

dispatch(); // process the request

