<?php

dispatch(POST, '^/contact/message\.json$', function() {
    if (!service('http')->isAjax()) {
        throw new BadMethodCallException("XHTTP request expected", 400);
    }

    require_once 'PHPUnit/Autoload.php';
    require_once 'PHPUnit/Framework/Assert/Functions.php';

    $message = service('http')->param('message', array());

    // ensure there were no hackish tries
    assertTrue(isset($message['sender']) && strlen($message['sender']) > 0);
    assertTrue(isset($message['content']) && strlen($message['content']) > 0);
    assertTrue(isset($message['email']) && (bool)preg_match('/^([a-zA-Z0-9_\.\-])+\@(([a-zA-Z0-9\-])+\.)+([a-zA-Z0-9]{2,4})+$/', $message['email']));

    service('db')->insert('messages', $message);
    //@TODO: send and email
    echo json_encode($message);
});

dispatch(GET, '^/contact$', function() {
    echo service('twig')->render('contact.html');
});
