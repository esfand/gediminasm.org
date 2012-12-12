<?php

dispatch(GET, '^/about$', function() {
    echo "about page";
});

dispatch(GET, '^/about.json$', function() {
    throw new Exception('Error occured', 403);
});
