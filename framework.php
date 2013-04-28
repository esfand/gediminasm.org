<?php

function dispatch($method = null, $route = null, callable $callback = null) {
    static $routes = [];
    static $before = []; // a list of all callbacks executed on every request
    if (null !== $method) {
        if (is_callable($method)) {
            $before[] = $method;
        } else {
            $routes[$method][] = [$route, $callback];
        }
        return; // nothing else to do
    }
    $uri = isset($_SERVER['REQUEST_URI']) ? $_SERVER['REQUEST_URI'] : '/';
    $uri = rawurldecode(($tmp = strtok($uri, '?#')) ? $tmp : $uri); // normalization

    $request_method = isset($_SERVER['REQUEST_METHOD']) ? $_SERVER['REQUEST_METHOD'] : 'GET';

    ob_start();
    // execute all pre handlers for every request, pass current $uri as arg
    for ($i = 0, $len = count($before); $i < $len; $before[$i]($uri), $i++);
    // first match a request method
    if (isset($routes[$request_method])) {
        // chose and execute matching route
        foreach ($routes[$request_method] as $handler) {
            list($route, $callback) = $handler;
            // try exact match
            if ($route === $uri || rtrim($uri, '/') === $route) {
                $callback(); break; // nothing else to do
            } else {
                // shift over static prefix
                for ($r = ltrim($route, '^'), $len = strlen($r), $i = 0; $i < $len && isset($uri[$i]) && $r[$i] === $uri[$i]; $i++);
                // if next char is regex type, try match it
                if (isset($r[$i]) && in_array($r[$i], ['[', '(', '.']) && preg_match("#{$route}#", $uri, $args)) {
                    call_user_func_array($callback, array_slice($args, 1));
                    break; // nothing else to look for
                }
            }
        }
    }
    if ($response = ob_get_clean()) {
        return $response; // always return response, its for user to decide whether he wants to escape it or filter
    }
    throw new LogicException("There was no route to match '{$_SERVER['REQUEST_METHOD']}:{$uri}' requested or response was empty", 404);
}

function service($name, callable $service = null) {
    static $services = [];
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
        return $instance ?: ($instance = call_user_func_array($service, [$config ?: ($config = require APP_DIR.'/config.php')]));
    };
}

