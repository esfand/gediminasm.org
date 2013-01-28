<?php

service('twig', function($config) {
    $loader = new Twig_Loader_Filesystem(APP_DIR . '/views');
    $twig = new Twig_Environment($loader, array(
        'cache' => APP_DIR . '/tmp/twig',
        'debug' => $config['twig']['debug'],
    ));
    // load twig extensions, should return extension instance when included
    foreach (glob(__DIR__.'/twig-extensions/*.php') as $file) {
        $twig->addExtension(include $file);
    }
    return $twig;
});

