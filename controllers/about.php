<?php

dispatch('GET', '/about', function() {
    echo service('twig')->render('about.html');
});

