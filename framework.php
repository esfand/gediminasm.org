<?php

// HTTP methods
define('HEAD', 1);
define('GET', 1);
define('POST', 2);
define('PUT', 4);
define('DELETE', 8);
define('ANY', GET|POST|PUT|DELETE);

function dispatch($method = null, $route = null, $callback = null) {
    static $routes = array();
    static $before = array(); // a list of all callbacks executed on every request
    if (null !== $method) {
        if (is_callable($method)) {
            $before[] = $method;
        } else {
            $routes[] = array($method, $route, $callback);
        }
        return; // nothing else to do
    }
    $uri = isset($_SERVER['REQUEST_URI']) ? $_SERVER['REQUEST_URI'] : '/';
    $uri = ($tmp = strtok($uri, '?#')) ? $tmp : $uri; // normalization

    $request_method = isset($_SERVER['REQUEST_METHOD']) && defined($_SERVER['REQUEST_METHOD']) ? constant($_SERVER['REQUEST_METHOD']) : GET;
    // force request_order to be GP, check that in php.ini better, since PHP 5.3. uncomment otherwise
    // $_REQUEST = array_merge($_GET, $_POST);

    ob_start();
    // execute all pre handlers for every request, pass current $uri as arg
    for ($i = 0, $len = count($before); $i < $len; $before[$i]($uri), $i++);
    // chose and execute matching route
    foreach ($routes as $handler) {
        list($method, $route, $callback) = $handler;
        // first match a request method
        if (($method & $request_method) === $request_method) {
            // method is OK, try exact match
            if (($r = trim($route, '^$')) === $uri || $r.'/' === $uri) {
                $callback(); break; // nothing else to do
            } else {
                // shift over static prefix, @TODO: prematch escape char ?
                for ($len = strlen($r), $i = 0; $i < $len && isset($uri[$i]) && ($r[$i] === $uri[$i] || $r[$i] === '\\'); $i++);
                // if next char is regex type, try match it
                if (isset($r[$i]) && in_array($r[$i], array('[', '(', '.')) && preg_match("#{$route}#", $uri, $args)) {
                    call_user_func_array($callback, array_slice($args, 1));
                    break; // nothing else to look for
                }
            }
        }
    }
    if ($response = ob_get_clean()) {
        return $response; // always return response, its for user to decide whether he wants to escape it or filter
    }
    throw new LogicException("There was no route to match '{$uri}' requested or response was empty", 404);
}

function service($name, Closure $service = null) {
    static $services = array();
    static $config;

    if (isset($services[$name])) {
        if (null === $service) return $services[$name]();
        throw new InvalidArgumentException("A service is already registered under {$name}");
    } elseif (null === $service) {
        throw new InvalidArgumentException("Unknown service {$name}");
    }
    $services[$name] = function() use ($service, &$config) {
        static $instance;
        // creates service instance once, gives $config as argument.
        return $instance ?: ($instance = $service($config ?: ($config = require APP_DIR.'/config.php')));
    };
}

