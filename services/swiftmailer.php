<?php

service('swiftmailer', function($config) {
    // initialize
    extract($config['swiftmailer']);
    $transport = Swift_SmtpTransport::newInstance('smtp.gmail.com', 465, 'ssl')
        ->setUsername($username)
        ->setPassword($password);

    return Swift_Mailer::newInstance($transport);
});

