<?php

use Michelf\Markdown;

dispatch(GET, '^/posts/(.+)/comments\.json$', function($postId) {
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
    OFFSET :offset LIMIT :limit
__SQL;

    $comments = service('db')->all($sql, compact('limit', 'offset', 'post_id'));
    $comments = array_map(function($c) {
        $c['created'] = service('time')->ago($c['created']);
        return $c;
    }, $comments);
    echo json_encode($comments);
});

dispatch(POST, '^/posts/(.+)/comment\.json$', function($postId) {
    if (!service('http')->isAjax()) {
        throw new BadMethodCallException("XHTTP request expected", 400);
    }

    $md = new Markdown;
    // do not allow html tags like injecting <script>
    $md->no_entities = true;
    $md->no_markup = true;

    $comment = service('http')->param('comment', array());

    require_once 'PHPUnit/Autoload.php';
    require_once 'PHPUnit/Framework/Assert/Functions.php';

    // ensure there were no hackish tries
    assertTrue(isset($comment['subject']) && strlen($comment['subject']) > 0);
    assertTrue(isset($comment['content']) && strlen($comment['content']) > 0);

    $comment['post_id'] = intval($postId);
    $comment['content'] = $md->transform($comment['content']);

    // should have some data when transformed from markdown
    assertTrue(isset($comment['content']) && strlen($comment['content']) > 0);

    service('db')->insert('comments', $comment);
    $comment['created'] = service('time')->ago(time());
    echo json_encode($comment);
});

