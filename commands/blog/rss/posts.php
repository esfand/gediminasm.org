<?php

use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

$cmd = $console->register('blog:rss:posts');

$cmd->setDescription('Generated blog rss feed');

$cmd->setCode(function(InputInterface $in, OutputInterface $out) {
    $RSS2 = <<<EOD
<rss version="2.0">
  <channel>
    <title>Gediminas Morkevicius blog posts</title>
    <link>http://gediminasm.org/</link>
    <description>Web blog about programming, innovation and best practices.</description>
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
        $post['summary'] = strip_tags($post['summary']);
        $post['created'] = date(DATE_RFC822, strtotime($post['created']));
        $items .= "\n".str_replace($keys, array_values($post), $ITEM);
    }
    file_put_contents($path = APP_DIR.'/public/rss.xml', str_replace(
        array('%items%', '%build_date%'),
        array($items, date(DATE_RFC822)),
        $RSS2
    ));
    $out->writeln(sprintf("Exported RSS to <info>{$path}</info>\n"));
});
