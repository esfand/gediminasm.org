<?php

service('db', function($config) {
    class _DB {
        public $link;

        function __construct($connection_string) {
            if (!$this->link = pg_connect($connection_string)) {
                throw new InvalidArgumentException(pg_last_error($this->link) ?: "Failed to connect to postgres using: {$connection_string}");
            }
        }

        function __destruct() {
            if (!pg_close($this->link)) {
                throw new InvalidArgumentException(pg_last_error($this->link) ?: "Failed to close postgres connection");
            }
        }

        function query($sql, array $args = array()) {
            count($args) && ($sql = $this->mapsql($sql, $args));
            if (!$result = pg_query($this->link, $sql)) {
                throw new InvalidArgumentException(pg_last_error($this->link) ?: "Failed to execute sql '{$sql}'");
            }
            return $result;
        }

        function all($sql, array $args = array()) {
            return pg_fetch_all($this->query($sql, $args)) ?: array();
        }

        function assoc($sql, array $args = array()) {
            return pg_fetch_assoc($this->query($sql, $args)) ?: null;
        }

        function column($sql, array $args = array(), $column = 0) {
            if (!$arr = pg_fetch_array($this->query($sql, $args))) {
                throw new InvalidArgumentException("'$sql' failed to produce expected result");
            }
            if (!isset($arr[$column])) {
                throw new InvalidArgumentException("Column $column was not found in result");
            }
            return $arr[$column];
        }

        function insert($table, array $data) {
            $sql = "INSERT INTO {$table} (" . implode(', ', array_keys($data)) . ')'
                . ' VALUES (' . implode(', ', array_fill(0, count($data), '?')) . ')';
            return $this->query($sql, array_values($data));
        }

        function update($table, array $data, array $where = array()) {
            $sql  = 'UPDATE ' . $table . ' SET ' . implode(' = ?, ', array_keys($data)) . ' = ?'
                . ' WHERE ' . implode(' = ? AND ', array_keys($where)) . ' = ?';
            $params = array_merge(array_values($data), array_values($where));
            return $this->query($sql, $params);
        }

        function mapsql($sql, array $args, Closure $mapper = null) {
            $formatter = function($v) {
                return is_string($v) ? pg_escape_literal($this->link, $v) : $v;
            };
            if (is_int($i = key($args)) && $i === 0) {
                $sql = preg_replace_callback('#\?#sm', function($m) use($args, &$i, &$mapper, &$formatter) {
                    if (!isset($args[$i])) {
                        throw new InvalidArgumentException("Psql: Missing an argument in query for ? mark");
                    }
                    return $mapper ? $mapper($i, $args[$i++], $formatter) : $formatter($args[$i++]);
                }, $sql);
            } else {
                $search = $replace = array();
                foreach ($args as $k => $v) {
                    $search[] = ':'.$k;
                    $replace[] = $mapper ? $mapper($k, $v, $formatter) : $formatter($v);
                }
                $sql = str_replace($search, $replace, $sql);
            }
            return $sql;
        }
    }

    // initialize
    $conn_str = '';
    foreach ($config['db'] as $key => $val) {
        $conn_str .= $key . '=' . $val . ' ';
    }
    return new _DB($conn_str . "options='--client_encoding=UTF8 --timezone=UTC'");
});

