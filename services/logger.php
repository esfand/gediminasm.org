<?php

service('logger', function(array $config) {
    class _Logger {

        private $handle;
        private $stack = array();

        function __construct($path) {
            if (!$this->handle = fopen($path, 'a+')) {
                throw new InvalidArgumentException("Failed to open log file [{$path}] for writting.");
            }
        }

        function __destruct() {
            if (!fclose($this->handle)) {
                throw new RuntimeException("Failed to close log file");
            }
        }

        function push($msg) {
            $this->stack[] = $msg; // save some cpu cycles, instead of generating timestamps for each info msg
            return $this;
        }

        function flush() {
            fwrite($this->handle, sprintf("%s ==>\n", date('Y-m-d H:i:s')));
            while ($msg = array_pop($this->stack)) {
                fwrite($this->handle, sprintf("    --> %s\n", $msg));
            }
        }
    }

    return new _Logger(APP_DIR . '/tmp/logs/app.log');
});

