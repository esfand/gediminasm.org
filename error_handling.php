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
    ob_end_clean(); // clean any output produced before
    // expects exception code to be HTTP code
    http_response_code($code = $e->getCode() ?: 500); // create status code header
    $trace = array_map(function($row) {
        $c = function($a, $d = null) use (&$row) {
            return isset($row[$a]) ? $row[$a] : ($d !== null ? $d : '');
        };
        $args = ($args = $c('args')) ? implode(', ', array_map(function($a) {
            return is_array($a) || is_object($a) ? gettype($a) : (string)$a;
        }, $args)) : '';
        return sprintf(
            '%s%s%s(%s) at %s:%s', $c('class'), $c('type'), $c('function'),
            $args, str_replace(APP_DIR, '', $c('file', 'n/a')), $c('line', 'n/a')
        );
    }, $e->getTrace());
    service('logger')->addError("Caught [{$code}] exception: " . $e->getMessage(), $trace);
    if (APP_ENV === 'production') {
        // first check for error file by code
        if (file_exists($efile = APP_DIR.'/public/'.$code.'.html')) {
            echo file_get_contents($efile);
        } else {
            echo "<h1>The service is currently down.</h1>";
            echo "<p>Come back later</p>";
        }
    } else {
        // assume debug
        echo '<h1>' . $e->getMessage() . '</h1>';
        echo implode('<br />', $trace);
    }
});

// changes exception handler for all urls ending like ".json"
dispatch(function($uri) {
    if (substr($uri, -5) === '.json') { // request param can be also used to determine format
        // override exception handler
        set_exception_handler(function(Exception $e) {
            http_response_code($code = $e->getCode() ?: 500); // create status code header
            service('logger')->addError("Caught [{$code}] exception: " . $e->getMessage(), $e->getTrace());
            service('http')->json(array('error' => array(
                'message' => $e->getMessage(),
                'code' => $code
            )));
        });
    }
});

