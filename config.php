<?php

// default configurations for services used (best production mode as default)
$conf = array(
    'db' => array(
        'name' => 'freelance_'.APP_ENV
    ),
);

// environment specific changes, can be moved to localized dist file
switch (APP_ENV) {
    case 'development':
        $conf['db']['name'] = 'dev_database';
        break;
    case 'testing':
        //
        break;
}

// return the final version of $conf
return $conf;
