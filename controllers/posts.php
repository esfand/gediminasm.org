<?php

$posts = function() {
    echo 'news';
};

dispatch(GET, '/', $posts);

dispatch(GET, '/posts', $posts);

dispatch(GET, '^/post/(.+)$', function($name) {
    $db = service('db');
    $sql = <<<__SQL
    SELECT n.summary
    FROM news AS n
    LIMIT :page, :limit
__SQL;

    $db->fetch_assoc($sql, compact('page', 'limit'), function(array $row) {
        var_dump($row);
    });
    service('db');
    service('http');
    die('done');
    echo "naujienu";
});
