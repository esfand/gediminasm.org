<?php

use Behat\Behat\Context\BehatContext;
use Behat\Gherkin\Node\PyStringNode,
    Behat\Gherkin\Node\TableNode;
use Behat\Behat\Context\Step\Then,
    Behat\Behat\Context\Step\When,
    Behat\Behat\Context\Step\Given;

require_once 'PHPUnit/Autoload.php';
require_once 'PHPUnit/Framework/Assert/Functions.php';

class DatabaseContext extends BehatContext {

    function __construct() {
        require_once APP_DIR . '/services/db.php';
    }

    /**
     * @BeforeScenario
     */
    function flushDatabase() {
        service('db')->query('TRUNCATE posts, comments, messages RESTART IDENTITY CASCADE');
    }

    /**
     * @Given /^I have a post "([^"]*)" with body:$/
     */
    function iHaveAPostWithBody($title, PyStringNode $body) {
        service('db')->insert('posts', array(
            'title' => $title,
            'summary' => 'summary of ' . $title,
            'content' => trim((string)$body),
            'slug' => str_replace(' ', '-', strtolower($title)),
            'created' => date('Y-m-d H:i:s', strtotime('-3 hours')),
        ));
    }

    /**
     * @Given /^I have a post "([^"]*)" comment "([^"]*)" with body:$/
     */
    function iHaveAPostComment($postTitle, $subject, PyStringNode $body) {
        $postId = service('db')->column('SELECT id FROM posts WHERE title = ?', array($postTitle));
        service('db')->insert('comments', array(
            'subject' => $subject,
            'author' => 'Behat Mink',
            'content' => trim((string)$body),
            'post_id' => intval($postId),
        ));
    }
}
