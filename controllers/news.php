<?php

dispatch(GET, '^/$', function() {
    echo "naujienos";
});

dispatch(GET, '^/news/summaries.json$', function() {
    echo "naujienu";
});
