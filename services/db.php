<?php

service('db', function($config) {
    class Database extends mysqli {

        function error($msg) {
            throw new Exception("Mysqli error: {$msg}");
        }

        function __destruct() {
            $this->clean_stored();
            $this->close();
        }

        function clean_stored() {
            while ($this->more_results() && $this->next_result()) {
                if ($res = $this->store_result()) {
                    $res->free();
                }
            }
        }

        function map_args($sql, array $args) {
            if (is_int($i = key($args)) && $i === 0) {
                $sql = preg_replace_callback('#\?#sm', function($m) use($args, &$i) {
                    if (!isset($args[$i])) {
                        $this->error("Missing an argument in query for ? mark");
                    }
                    return is_string($args[$i]) ? $this->real_escape_string($args[$i++]) : $args[$i++];
                }, $sql);
            } else {
                $search = array_map(function($k) {
                    return ':'.$k;
                }, array_keys($args));
                $replace = array_map(function($v) {
                    return is_string($v) ? $this->real_escape_string($v) : $v;
                }, $args);
                $sql = str_replace($search, $replace, $sql);
            }
            return $sql;
        }

        function fetch_assoc($sql, array $args, Closure $callback) {
            count($args) && ($sql = $this->map_args($sql, $args));
            if ($result = $this->query($sql)) {
                $i = 0;
                while ($row = $result->fetch_assoc()) {
                    $callback($row, $i++);
                }
                $result->free();
            }
        }

    }
    $db = @new Database($config['host'], $config['user'], $config['pass'], $config['name'], $config['port']);
    if ($err = mysqli_connect_error()) {
        $db->error($err);
    }
    if (!$db->query('SET NAMES \'utf8\'')) {
        $db->error("utf8 must be supported");
    }
    return $db;
});

