<?php

use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Michelf\Markdown;

$cmd = $console->register('blog:update');

$cmd->setDescription('Syncronizes blog posts with current markdown docs');

$cmd->setCode(function(InputInterface $in, OutputInterface $out) {
    $lookup = array(
        'javascript-inheritance' => array(
            'slug' => 'using-prototypal-inheritance-in-javascript',
            'title' => 'Using prototypal inheritance in javascript',
            'created' => new DateTime('2012-05-26 01:12:29'),
            'meta' => 'How to apply OOP style in javascript and make it extensible, strict and dynamic'
        ),
        'doctrine-zend' => array(
            'slug' => 'doctrine-2-on-zend-framework',
            'title' => 'Doctrine 2 on Zend framework',
            'created' => new DateTime('2010-07-15 22:22:24'),
            'meta' => 'How to integrate doctrine2 on zend framework'
        ),
        'compile-php' => array(
            'slug' => 'compile-php',
            'title' => 'Compile php on your own',
            'created' => new DateTime('2010-08-16 22:26:47'),
            'meta' => 'How to compile php'
        ),
        'smarty' => array(
            'slug' => 'smarty-3-extension-for-zend-framework',
            'title' => 'Smarty 3 extension for Zend Framework',
            'created' => new DateTime('2010-10-13 20:21:39'),
            'meta' => 'Smarty 3 extension for Zend framework, with full: layout and view template support'
        ),
        'window-manager' => array(
            'slug' => 'dwm-window-manager',
            'title' => 'DWM window manager for linux',
            'created' => new DateTime('2013-03-03 20:00:00'),
            'meta' => 'Why to use window manager instead of desktop and why it should be DWM, what tools fit together'
        ),
    );
    $out->writeln(sprintf("<info>Scanning for posts..</info>\n"));
    $db = service('db');
    $db->query('BEGIN');
    try {
        foreach ($lookup as $name => $post) {
            $d = APP_DIR . '/resources/posts/' . $name;
            if (!is_dir($d)) throw new Exception("Could not find post {$name}");

            extract($post); // slug and title
            $sql = "SELECT title, summary, content, meta FROM posts WHERE slug = :slug LIMIT 1";
            $old = $db->assoc($sql, compact('slug'));
            if ($old) {
                $out->writeln("Found in db <comment>{$old['title']}</comment>\n");
                $update = array();
                $content = Markdown::defaultTransform(file_get_contents($d.'/content.md'));
                $summary = Markdown::defaultTransform(file_get_contents($d.'/summary.md'));
                foreach (array('content', 'summary', 'title', 'meta') as $key) {
                    if ($$key !== $old[$key]) {
                        $update[$key] = $$key;
                    }
                }
                if ($update) {
                    $out->writeln("Updating <comment>{$title}</comment>");
                    $db->update('posts', $update, compact('slug'));
                    $db->query('UPDATE posts SET updated = NOW() WHERE slug = ?', array($slug));
                }
            } else {
                $out->writeln("Inserting in db <comment>{$title}</comment>");
                $db->insert('posts', array(
                    'slug' => $slug,
                    'title' => $title,
                    'meta' => $meta,
                    'content' => Markdown::defaultTransform(file_get_contents($d.'/content.md')),
                    'summary' => Markdown::defaultTransform(file_get_contents($d.'/summary.md')),
                    'created' => $created->format('Y-m-d H:i:s'),
                ));
            };
        }
        $db->query('COMMIT');
    } catch (Exception $e) {
        $db->query('ROLLBACK');
        throw $e;
    }
});
