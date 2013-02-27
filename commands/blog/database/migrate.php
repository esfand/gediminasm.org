<?php

use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

$cmd = $console->register('blog:database:migrate');

$cmd->setDescription('Migrates old comments');

$cmd->setCode(function(InputInterface $in, OutputInterface $out) {

    $env = $in->getOption('env');

    $out->writeLn('<info>Started: </info><comment>'.$env.'</comment>');
    $out->writeLn('');

    // initialize link with correct database
    $psql = service('db');
    $mysql = new PDO('mysql:dbname=old_blog', 'root', 'nimda');
    $mysql->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    $psql->query('BEGIN');
    try {
        $stmt = $mysql->prepare('SELECT c.author, c.content, c.subject, c.created FROM comments AS c LEFT JOIN articles AS a ON c.article_id = a.id WHERE a.slug = :slug');
        foreach ($psql->all('SELECT id, slug FROM posts') as $post) {
            $stmt->execute(array(':slug' => $post['slug'] === 'compile-php' ? 'build-php-5-3-0-php-5-3-4-dev-on-ubuntu-server' : $post['slug']));
            foreach ($stmt->fetchAll(PDO::FETCH_ASSOC) as $comment) {
                $out->writeLn("Post {$post['slug']} - <info>{$comment['subject']}</info>");
                $comment['post_id'] = intval($post['id']);
                $psql->insert('comments', $comment);
            }
        }
        $psql->query('COMMIT');
    } catch (Exception $e) {
        $psql->query('ROLLBACK');
        throw $e;
    }
    $out->writeLn('<info>Done.</info>');
});

