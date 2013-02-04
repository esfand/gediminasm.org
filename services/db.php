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

        function quote($input) {
            $s = "'". $this->real_escape_string($input) ."'";
            return strlen($input) ? str_replace("''", "'", $s) : $s; // in case if was quoted in sql
        }

        function map_args($sql, array $args) {
            if (is_int($i = key($args)) && $i === 0) {
                $sql = preg_replace_callback('#\?#sm', function($m) use($args, &$i) {
                    if (!isset($args[$i])) {
                        $this->error("Missing an argument in query for ? mark");
                    }
                    return is_string($args[$i]) ? $this->quote($args[$i++]) : $args[$i++];
                }, $sql);
            } else {
                $search = array_map(function($k) {
                    return ':'.$k;
                }, array_keys($args));
                $replace = array_map(function($v) {
                    return is_string($v) ? $this->quote($v) : $v;
                }, $args);
                $sql = str_replace($search, $replace, $sql);
            }
            return $sql;
        }

        function insert($table, array $data) {
            $sql = "INSERT INTO {$table} (" . implode(', ', array_keys($data)) . ')'
                . ' VALUES (' . implode(', ', array_fill(0, count($data), '?')) . ')';
            if (!$this->query($this->map_args($sql, array_values($data)))) {
                $this->error($this->error ?: "Failed to insert into {$table}");
            }
            return $this->insert_id;
        }

        function update($table, array $data, array $where = array()) {
            $sql  = 'UPDATE ' . $table . ' SET ' . implode(' = ?, ', array_keys($data)) . ' = ?'
                . ' WHERE ' . implode(' = ? AND ', array_keys($where)) . ' = ?';
            $params = array_merge(array_values($data), array_values($where));
            if (!$this->query($this->map_args($sql, $params))) {
                $this->error($this->error ?: "Failed to update {$table}");
            }
        }

        function first($sql, array $args = array()) {
            count($args) && ($sql = $this->map_args($sql, $args));
            $ret = null;
            if (($result = $this->query($sql)) && ($ret = $result->fetch_assoc())) {
                $result->free();
            }
            return $ret;
        }

        function all($sql, array $args = array()) {
            $ret = array();
            $this->each($sql, $args, function($row) use (&$ret) {
                $ret[] = $row;
            });
            return $ret;
        }

        function each($sql, array $args, Closure $callback) {
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

    // initialize
    extract($config['db']);
    $db = new Database($host, $user, $pass, $name, $port);
    if ($err = mysqli_connect_error()) {
        $db->error($err);
    }
    if (!$db->query("SET NAMES 'utf8'")) {
        $db->error($db->error ?: "utf8 must be supported");
    }
    if (!$db->query("SET time_zone = '+0:00'")) {
        $db->error($db->error ?: "Failed to set UTC timezone");
    }
    return $db;
});

