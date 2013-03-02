<?php

use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

$cmd = $console->register('blog:rss:comments');

$cmd->setDescription('Generate all blog post comment feeds');

$cmd->setCode(function(InputInterface $in, OutputInterface $out) {
    $RSS2 = <<<EOD
<rss version="2.0">
  <channel>
    <title>Gedi blog post - %title%</title>
    <link>http://gediminasm.org/post/%slug%#comments</link>
    <description>Gediminas Morkevicius comment feed for blog post: %title%</description>
    <language>en-us</language>
    <copyright>Copyright 2013 Gediminas Morkevicius</copyright>
    <lastBuildDate>%build_date%</lastBuildDate>
    <managingEditor>gediminas.morkevicius@gmail.com</managingEditor>
    <webMaster>gediminas.morkevicius@gmail.com</webMaster>%items%
  </channel>
</rss>
EOD;
    $ITEM = <<<EOD
<item>
    <title>%subject%</title>
    <description>%content%</description>
    <link>http://gediminasm.org/post/%slug%#comments</link>
    <pubDate>%created%</pubDate>
</item>
EOD;

    $out->writeln(sprintf("<info>Loading all posts..</info>\n"));
    if (!is_dir($dir = APP_DIR."/public/feeds") && !mkdir($dir, 0775, true)) {
        throw new RuntimeException("Failed to create RSS feed directory [{$dir}] check permissions.");
    }
    $db = service('db');
    $sql = 'SELECT id, slug, title FROM posts';
    foreach ($db->all($sql) as $post) {
        $items = '';
        $out->writeln("Generating RSS comment feed for <comment>{$post['title']}</comment>");

        foreach ($db->all('SELECT subject, content, created FROM comments WHERE post_id = ? ORDER BY created DESC', array($post['id'])) as $comment) {
            $comment['slug'] = $post['slug'];
            $comment['subject'] = htmlentities($comment['subject'], ENT_XML1);
            $keys = array_map(function($v) {return '%'.$v.'%';}, array_keys($comment));
            $comment['content'] = htmlentities(substr(strip_tags($comment['content']), 0, 300), ENT_XML1).'..';
            $comment['created'] = date(DATE_RFC822, strtotime($comment['created']));
            $items .= "\n".str_replace($keys, array_values($comment), $ITEM);
        }
        $post['build_date'] = date(DATE_RFC822);
        $post['items'] = $items;
        $keys = array_map(function($v) {return '%'.$v.'%';}, array_keys($post));
        file_put_contents($path = $dir."/{$post['slug']}.xml", str_replace($keys, array_values($post), $RSS2));
        $out->writeln(sprintf("Exported RSS commend feed to <info>{$path}</info>"));
    }
});
