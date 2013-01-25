<?php

// default configurations for services used
$conf = array(
    'db' => array(
        'name' => 'blog',
        'host' => 'localhost',
        'port' => 3306,
        'user' => 'root',
        'pass' => 'nimda',
    ),
);

// environment specific changes, can be moved to localized dist file
switch (APP_ENV) {

    case 'production':
        $conf['db']['pass'] = 'secret';
        break;

    case 'testing':
        $conf['db']['name'] = 'blog_test';
        break;

    case 'development':
    default:
        $conf['db']['pass'] = 'nimda';
        break;
}

// return the final version of $conf
return $conf;
