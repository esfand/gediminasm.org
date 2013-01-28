<?php

class twig_menu extends Twig_Extension {

    function getFunctions() {
        return array(
            'menu' => new Twig_Function_Method($this, 'menu', array('is_safe' => array('html'))),
        );
    }

    function menu() {
        $uri = isset($_SERVER['REQUEST_URI']) ? $_SERVER['REQUEST_URI'] : '/';
        $menu = array(
            array(
                'title' => 'Blog Posts',
                'url' => '/',
                'active' => $uri === '/' || stripos($uri, 'post') !== false || stripos($uri, 'article') !== false,
            ),
            array(
                'title' => 'About Me',
                'url' => '/about',
                'active' => strpos($uri, '/about') === 0,
            ),
            array(
                'title' => 'Contact',
                'url' => '/contact',
                'active' => strpos($uri, '/contact') === 0,
            ),
        );
        $out = '<ul class="nav">';
        foreach ($menu as $entry) {
            extract($entry);
            $out .= '<li' . ($active ? ' class="active"' : '') . '>';
            $out .= '<a href="'.$url.'">' . $title . '</a>';
            $out .= '</li>';
        }
        return $out .= '</ul>';
    }

    function getName() {
        return 'menu';
    }
}

return new twig_menu();
