<?php

dispatch(GET, '^/posts/(.+)/comments$', function($postId) {
    if (!service('http')->isAjax()) {
        throw new BadMethodCallException("XHTTP request expected", 400);
    }

    $limit = intval(service('http')->param('limit', 10));
    $offset = intval(service('http')->param('offset', 0));
    $post_id = intval($postId);

    $sql = <<<__SQL
    SELECT c.author, c.subject, c.content, c.created
    FROM comments AS c
    WHERE c.post_id = :post_id
    ORDER BY c.created DESC
    LIMIT :offset, :limit
__SQL;

    $comments = service('db')->all($sql, compact('limit', 'offset', 'post_id'));
    echo json_encode($comments);
});

