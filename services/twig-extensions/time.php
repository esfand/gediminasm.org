<?php

class twig_time extends Twig_Extension {

    function getFilters() {
        return array(
            'ago' => new Twig_Filter_Method($this, 'ago', array('is_safe' => array('html'))),
        );
    }

    function ago($timestamp) {
        if (is_string($timestamp)) {
            $timestamp = new DateTime($timestamp);
        }
        if ($timestamp instanceof DateTime) {
            $timestamp = $timestamp->getTimestamp();
        }
        $ago = time() - $timestamp;

        if ($ago < 1) {
            $ago = 'a moment ago';
        } else {
            $a = array(
                12 * 30 * 24 * 60 * 60  =>  'year',
                30 * 24 * 60 * 60       =>  'month',
                24 * 60 * 60            =>  'day',
                60 * 60                 =>  'hour',
                60                      =>  'minute',
                1                       =>  'second',
            );

            foreach ($a as $secs => $str) {
                $d = $ago / $secs;
                if ($d >= 1) {
                    $r = round($d);
                    $ago = $r . ' ' . $str . ($r > 1 ? 's' : '') . ' ago';
                    break;
                }
            }
        }
        return $ago;
    }

    function getName() {
        return 'time';
    }
}

return new twig_time();
