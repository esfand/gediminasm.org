<?php

class twig_time extends Twig_Extension {

    function getFilters() {
        return array(
            'ago' => new Twig_Filter_Method($this, 'ago', array('is_safe' => array('html'))),
        );
    }

    function ago($timestamp) {
        return service('time')->ago($timestamp);
    }

    function getName() {
        return 'time';
    }
}

return new twig_time();
