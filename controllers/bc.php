<?php

$bc = array(
    'softdeleteable.md' => 'softdeleteable-behavior-extension-for-doctrine-2',
    'symfony2.md' => 'install-gedmo-doctrine2-extensions-in-symfony2',
    'mapping.md' => 'mapping-extension-for-doctrine2',
    'annotations.md' => 'annotation-reference',
    'loggable.md' => 'loggable-behavioral-extension-for-doctrine2',
    'timestampable.md' => 'timestampable-behavior-extension-for-doctrine-2',
    'tree.md' => 'tree-nestedset-behavior-extension-for-doctrine-2',
    'sluggable.md' => 'sluggable-behavior-extension-for-doctrine-2',
    'translatable.md' => 'translatable-behavior-extension-for-doctrine-2',
);

foreach ($bc as $doc => $article) {
    dispatch(GET, '/article/' . $article, function() use ($doc) {
        service('http')->redirect('https://github.com/l3pp4rd/DoctrineExtensions/blob/master/doc/' . $doc, 301);
    });
}

dispatch(GET, '^/article/(.+)$', function($post) {
    service('http')->redirect('/post/' . $post, 301);
});

dispatch(GET, '^/article/build-php-5-3-0-php-5-3-4-dev-on-ubuntu-server$', function() {
    service('http')->redirect('/post/compile-php', 301);
});

dispatch(GET, '^/demo.*', function() {
    echo service('twig')->render('demo.html');
});

