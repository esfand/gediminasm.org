<?php

dispatch(GET, '^/about$', function() {
    echo service('twig')->render('about.html');
});

dispatch(GET, '^/contact$', function() {
    throw new Exception('Error occured', 403);
});
