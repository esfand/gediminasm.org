<?php

service('db', function() {
    class Database {

        public $conn;

        private function error($msg) {
            throw new Exception("Mysqli error: {$msg}");
        }

        function connect() {
            $this->conn = new mysqli('localhost', 'root', 'nimda', 'freelance'.(isset($_SERVER['TEST']) ? '_test' : ''));
            if ($err = mysqli_connect_error()) {
                $this->error($err);
            }
            if (!$this->conn->query('SET NAMES \'utf8\'')) {
                $this->error("utf8 must be supported");
            }

            return $this;
        }

        function assoc($sql, $args) {
            //
        }

    }
    $db = new Database;
    return $db->connect();
});
