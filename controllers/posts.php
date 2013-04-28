<?php

$posts = function() {
    $sql = <<<__SQL
    SELECT p.summary, p.created, p.title, p.slug, (
        SELECT COUNT(c.post_id)
        FROM comments AS c
        WHERE c.post_id = p.id
    ) AS num_comments
    FROM posts AS p
    ORDER BY p.created DESC
__SQL;

    $posts = service('db')->all($sql);
    echo service('twig')->render('posts/list.html', compact('posts'));
};

dispatch('GET', '/', $posts);

dispatch('GET', '/posts', $posts);

dispatch('GET', '^/post/(.+)/?', function($name) {
    $sql = 'SELECT p.* FROM posts AS p WHERE p.slug = ? LIMIT 1';
    if (!$post = service('db')->assoc($sql, array(rtrim($name, '/')))) {
        throw new InvalidArgumentException("Post [{$name}] was not found", 404);
    }

    $sql = "UPDATE posts SET views = views + 1 WHERE id = ?";
    service('db')->query($sql, array($post['id']));
    echo service('twig')->render('posts/view.html', compact('post'));
});
