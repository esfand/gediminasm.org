<?php

class _routing extends PHPUnit_Framework_TestCase {

    protected function setUp() {
        dispatch(GET, '^/_tests/post/([\d]+)$', function($id) { echo 'Post: '.intval($id); });
        dispatch(GET|POST|DELETE|PUT, '/_simple', function() { echo 'OK'; });
        dispatch(GET, '^/_post/(.+)/page/([\d]+)/sort/(.+)$', function($name, $page, $sort) { echo "Post $name on $page sort $sort"; });
        dispatch(ANY, '^/_post/(.+)$', function($name) { echo 'Post by name: '.$name; });
        dispatch(ANY, '^/_post/(.+)/somth$', function($name) { echo 'Will not match, above will take precedence'; });
    }

    /**
     * @test
     * @dataProvider getActions
     */
    function shouldHandleActionResponseForSpecificRoute($method, $uri, $code, $response = null) {
        try {
            $_SERVER['REQUEST_URI'] = $uri;
            $_SERVER['REQUEST_METHOD'] = $method;
            $res = dispatch();
        } catch (Exception $e) {
            assertEquals($e->getCode(), $code);
            return;
        }
        assertTrue($code === 200 && $res === $response);
    }

    function getActions() {
        return array(
            array('GET', '/_tests/post/5', 200, 'Post: 5'),
            array('POST', '/_tests/post/5', 404),
            array('GET', '/_tests/post/new', 404),
            array('GET', '/_simple', 200, 'OK'),
            array('PUT', '/_simple', 200, 'OK'),
            array('DELETE', '/_simple', 200, 'OK'),
            array('POST', '/_simpl', 404),
            array('GET', '/_simple/', 404),
            array('GET', '/_post/a_post_name', 200, 'Post by name: a_post_name'),
            array('GET', '/_post/name/page/3/sort/popularity', 200, 'Post name on 3 sort popularity'),
            array('GET', '/_post/name/somth', 200, 'Post by name: name/somth'),
        );
    }
}
