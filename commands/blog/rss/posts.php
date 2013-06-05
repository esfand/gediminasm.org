<?php

use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

$cmd = $console->register('blog:rss:posts');

$cmd->setDescription('Generated blog rss feed');

$cmd->setCode(function(InputInterface $in, OutputInterface $out) {
    $RSS2 = <<<EOD
<rss version="2.0" xmlns:atom="http://www.w3.org/2005/Atom">
  <channel>
    <title>Gediminas Morkevicius blog posts</title>
    <link>http://gediminasm.org/</link>
    <description>Web blog about programming, innovation and best practices.</description>
    <language>en-us</language>
    <copyright>Copyright 2013 Gediminas Morkevicius</copyright>
    <atom:link href="http://gediminasm.org/rss.xml" rel="self" type="application/rss+xml" />
    <lastBuildDate>%build_date%</lastBuildDate>
    <managingEditor>gediminas.morkevicius@gmail.com (Gediminas Morkevičius)</managingEditor>
    <webMaster>gediminas.morkevicius@gmail.com (Gediminas Morkevičius)</webMaster>%items%
  </channel>
</rss>
EOD;
    $ITEM = <<<EOD
<item>
    <title>%title%</title>
    <description>%summary%</description>
    <link>http://gediminasm.org/post/%slug%</link>
    <comments>http://gediminasm.org/post/%slug%#comments</comments>
    <guid>http://gediminasm.org/post/%slug%</guid>
    <pubDate>%created%</pubDate>
</item>
EOD;

    $out->writeln(sprintf("<info>Loading all posts..</info>\n"));

    $db = service('db');
    $sql = 'SELECT slug, title, summary, created, updated FROM posts';
    $items = '';
    foreach ($db->all($sql) as $post) {
        $out->writeln("RSS <comment>{$post['title']}</comment>\n");
        $keys = array_map(function($v) {return '%'.$v.'%';}, array_keys($post));
        $post['summary'] = htmlentities(substr(strip_tags($post['summary']), 0, 300)).'..';
        $post['created'] = date('D, d M Y H:i:s O', strtotime($post['created']));
        $items .= "\n".str_replace($keys, array_values($post), $ITEM);
    }
    file_put_contents($path = APP_DIR.'/public/rss.xml', str_replace(
        array('%items%', '%build_date%'),
        array($items, date('D, d M Y H:i:s O')),
        $RSS2
    ));
    $out->writeln(sprintf("Exported RSS to <info>{$path}</info>\n"));
});
