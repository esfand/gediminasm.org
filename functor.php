<?php

// HTTP methods
define('GET', 1);
define('POST', 2);
define('PUT', 4);
define('DELETE', 8);
define('HEAD', 16);
define('ANY', GET|POST|PUT|DELETE|HEAD);

function dispatch($method = null, $route = null, $callback = null) {
    static $routes = array();
    if (null !== $method) {
        if (is_callable($route)) {
            $callback = $route;
            $route = '*';
        }
        // just register route
        $routes[] = array($method, $route, $callback);
        return; // nothing else to do
    }
    $uri = isset($_SERVER['REQUEST_URI']) ? $_SERVER['REQUEST_URI'] : '/';
    // normalization
    $uri = (false !== strpos($uri, '?')) ? $uri = strstr($uri, '?', true) : $uri;
    $uri = (false !== strpos($uri, '#')) ? $uri = strstr($uri, '#', true) : $uri;

    $request_method = constant(isset($_SERVER['REQUEST_METHOD']) ? $_SERVER['REQUEST_METHOD'] : 'GET');
    // force request_order to be GP
    $_REQUEST = array_merge($_GET, $_POST);

    foreach ($routes as $handler) {
        list($method, $route, $callback) = $handler;
        if (($method & $request_method) === $request_method) {
            if ($route === '*' || preg_match("#{$route}#", $uri, $args)) {
                if (isset($args)) {
                    array_shift($args); // cleanup matches
                }
                $any = true;
                call_user_func_array($callback, isset($args) ? $args : array());
            }
        }
    }
    if (!$any) {
        throw new LogicException("There was no route to match '{$uri}' requested", 404);
    }
}

function service($name, Closure $service = null) {
    static $services = array();
    static $config;

    if (null !== $service) {
        // attempt to register, config will be also invoked only if service called
        if (isset($services[$name])) {
            throw new InvalidArgumentException("A service is already registered under $name");
        }
        $services[$name] = function() use ($service, &$config) {
            static $instance;
            return $instance ?: ($instance = $service($config ?: ($config = include APP_DIR.'/config.php')));
        };
    } else {
        if (!isset($services[$name])) {
            throw new InvalidArgumentException("Unknown service $name");
        }
        return $services[$name]();
    }
}

