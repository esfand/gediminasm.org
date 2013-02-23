<?php

service('logger', function($config) {
    class _Logger {

        private $handle;
        private $stack = array();
        private $path;

        private function open() {
            if (!is_dir($dir = dirname($this->path)) && !mkdir($dir, 775, true)) {
                die("Failed to create log directory [{$dir}] check permissions.");
            } elseif (!is_resource($this->handle = fopen($this->path, 'a+'))) {
                die("Failed to open log file [{$this->path}] for writting.");
            }
            return true;
        }

        function __construct($path) {
            $this->path = $path;
        }

        function __destruct() {
            if (is_resource($this->handle) && !fclose($this->handle)) {
                die("Failed to close log file");
            }
        }

        function push($msg) {
            $this->stack[] = $msg; // save some cpu cycles, instead of generating timestamps for each info msg
            return $this;
        }

        function flush() {
            is_resource($this->handle) || $this->open(); // open file to append
            fwrite($this->handle, sprintf("%s ==>\n", date('Y-m-d H:i:s')));
            while ($msg = array_pop($this->stack)) {
                fwrite($this->handle, sprintf("    --> %s\n", $msg));
            }
        }
    }

    return new _Logger($config['log_file']);
});

