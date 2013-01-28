<?php

// default configurations for services used
$conf = array(
    'db' => array(
        'name' => 'blog',
        'host' => 'localhost',
        'port' => 3306,
        'user' => 'root',
        'pass' => '',
    ),
    'twig' => array(
        'debug' => false,
    ),
);

// environment specific changes, can be moved to localized dist file
switch (APP_ENV) {

    case 'production':
        $conf['db']['pass'] = 'secret';
        break;

    case 'testing':
        $conf['db']['name'] = 'blog_test';
        $conf['twig']['debug'] = 'true';
        break;

    case 'development':
    default:
        $conf['twig']['debug'] = 'true';
        $conf['db']['pass'] = 'nimda';
        break;
}

// return the final version of $conf
return $conf;
