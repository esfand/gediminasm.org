<?php

use Behat\Behat\Context\BehatContext,
    Behat\MinkExtension\Context\MinkContext;
use Behat\Gherkin\Node\PyStringNode,
    Behat\Gherkin\Node\TableNode;
use Behat\Behat\Context\Step\Then,
    Behat\Behat\Context\Step\When,
    Behat\Behat\Context\Step\Given;

require_once 'PHPUnit/Autoload.php';
require_once 'PHPUnit/Framework/Assert/Functions.php';

class FeatureContext extends MinkContext {

    private $conf;

    function __construct(array $parameters) {
        $this->conf = $parameters;

        !defined('APP_ENV') && define('APP_ENV', 'testing');
        !defined('APP_DIR') && define('APP_DIR', $this->conf['root_dir']);

        require_once APP_DIR . '/framework.php';

        //prepare other feature contexts
        $this->useContext('services', new ServiceContext);
        $this->useContext('database', new DatabaseContext);
    }

    /**
     * @Then /^I should see "([^"]*)" as one of blog post titles$/
     */
    function iShouldSeeBlogPostTitle($title) {
        $el = $this->find('xpath', '//h2/a[contains(., "'.$title.'")]', 3);
        assertNotNull($el, "There was no post found by title: {$title}");
        return $el;
    }

    /**
     * @Then /^a post "([^"]*)" should be published "([^"]*)"$/
     */
    function aPostShouldBePublished($title, $ago) {
        $post = $this->iShouldSeeBlogPostTitle($title);
        $node = $post->find('xpath', '..')->find('xpath', '..')->find('css', 'span.date');
        assertTrue($node && $node->getText() == $ago, "Published: {$ago} does not match actual. Timezone ?");
    }

    /**
     * @Then /^I should see "([^"]*)" as a post body$/
     */
    function iShouldSeeTextAsPostBody($text) {
        $el = $this->find('xpath', '//div[contains(@class, "content") and contains(., "'.$text.'")]', 5);
        assertNotNull($el, "There was no post found containing: {$text} in body");
    }

    function search(\Closure $lookup, $retries = 10, $sleep = 1) {
        $result = false;
        do {
            $result = $lookup($this->getSession());
        } while (!$result && --$retries && sleep($sleep) !== false);
        return $result;
    }

    function findAll($type, $cond, $retries = 10) {
        return $this->search(function($s) use ($type, $cond, $retries) {
            return array_filter($s->getPage()->findAll($type, $cond), function($el) {
                return $el->isVisible();
            });
        }, $retries);
    }

    function find($type, $cond, $retries = 10) {
        return $this->search(function($s) use ($type, $cond, $retries) {
            $el = $s->getPage()->find($type, $cond);
            return $el && $el->isVisible() ? $el : null;
        }, $retries);
    }
}
