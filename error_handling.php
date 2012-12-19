<?php

// simple php error handler, transform all errors to exceptions
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

// default exception handler
set_exception_handler(function(Exception $e) {
    // do not use any fancy stuff which might throw another exception or catch it
    // expects status code to be HTTP code
    http_response_code($code = $e->getCode() ?: 500); // create status code header
    if (APP_ENV === 'production') {
        // first check for error file by code
        if (file_exists($efile = __DIR__.'/../public/'.$code.'.html')) {
            echo file_get_contents($efile);
        } else {
            echo "The service is currently down.";
        }
        return;
    }
    // default debug response
    echo $e->getMessage();
});

// overrides default exception handler for json requests
dispatch(ANY, '\.json$', function() {
    set_exception_handler(function(Exception $e) {
        http_response_code($code = $e->getCode() ?: 500); // create status code header
        service('http')->json(array('error' => array(
            'message' => $e->getMessage(),
            'code' => $code
        )));
    });
});

