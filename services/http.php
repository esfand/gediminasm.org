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

        function param($key, $default = null) {
            // @NOTE: if there is a + sign in your query parameter, extract it from $_SERVER['QUERY_STRING']
            return isset($_REQUEST[$key]) && $_REQUEST[$key] !== '' ? $_REQUEST[$key] : $default;
        }

        function isAjax() {
            return isset($_SERVER['HTTP_X_REQUESTED_WITH']) && strcasecmp('XMLHttpRequest', $_SERVER['HTTP_X_REQUESTED_WITH']) === 0;
        }
    }
    return new Http;
});
