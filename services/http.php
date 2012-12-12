<?php

service('http', function() {
    // do not expose the class
    class Http {

        function redirect($url, $code = 302, $exit = true) {
            ob_end_clean(); // clean any output produced before
            http_response_code($code); // create status code header
            header("Location: $url");
            if ($exit) {
                exit;
            }
        }

        function json($object, $jsonp_callback = null) {
            ob_end_clean(); // clean any output produced before
            header('Pragma: no-cache');
            header('Cache-Control: no-store, no-cache');
            $json = json_encode($object);
            if ($jsonp_callback) {
                header('Content-Type: text/javascript');
                echo "$jsonp_callback($json);";
            } else {
                header('Content-Type: application/json');
                echo $json;
            }
        }

        function file($path, $filename = null, $mime = null) {
            ob_end_clean(); // clean any output produced before
            header('Pragma: no-cache');
            header('Cache-Control: no-store, no-cache');
            if (null === $filename) {
                $filename = basename($path);
            }
            if (null === $mime) {
                $mime = finfo_file(finfo_open(FILEINFO_MIME_TYPE), $path);
            }
            header('Content-Type: ' . $mime);
            header('Content-Length: ' . filesize($path));
            header('Content-Disposition: attachment; filename="'.$filename.'"');
            readfile($path);
        }

        function cookie($key, $value = '', $expiry = null, $path = '/', $domain = null, $secure = false, $httponly = false) {
            if (null === $expiry) {
                $expiry = time() + (3600 * 24 * 30);
            }
            return setcookie($key, $value, $expiry, $path, $domain, $secure, $httponly);
        }

        // Adds to or modifies the current query string
        function query($key, $value = null) {
            $query = array();
            if (isset($_SERVER['QUERY_STRING'])) {
                parse_str($_SERVER['QUERY_STRING'], $query);
            }
            if (is_array($key)) {
                $query = array_merge($query, $key);
            } else {
                $query[$key] = $value;
            }

            $request_uri = isset($_SERVER['REQUEST_URI']) ? $_SERVER['REQUEST_URI'] : '/';
            if (strpos($request_uri, '?') !== false) {
                $request_uri = strstr($request_uri, '?', true);
            }
            return $request_uri . ($query ? '?' . http_build_query($query) : '');
        }
    }
    return new Http;
});
