<?php

dispatch(GET, '^/$', function() {
    echo "naujienos";
});

dispatch(GET, '^/news/summaries/page/([\d]+)/limit/([\d]+).json$', function($page, $limit) {
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
