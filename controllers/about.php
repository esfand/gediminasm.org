<?php

dispatch(GET, '^/about$', function() {
    echo "about page";
});

dispatch(GET, '^/contact$', function() {
    throw new Exception('Error occured', 403);
});
