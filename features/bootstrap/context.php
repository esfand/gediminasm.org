<?php

use Behat\Behat\Context\BehatContext,
    Behat\MinkExtension\Context\MinkContext;
use Behat\Gherkin\Node\PyStringNode,
    Behat\Gherkin\Node\TableNode;
use Behat\Behat\Context\Step\Then,
    Behat\Behat\Context\Step\When,
    Behat\Behat\Context\Step\Given;

class FeatureContext extends MinkContext {

    private $conf;

    function __construct(array $parameters) {
        $this->conf = $parameters;

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

    /**
     * @Then /^I should see a comment "([^"]*)" containing message "([^"]*)"$/
     */
    function iShouldSeeCommentContainingMessage($subject, $message) {
        $el = $this->find('xpath', '//div[contains(@class, "comment-title")]/span[contains(@class, "subject") and contains(., "'.$subject.'")]', 5);
        assertNotNull($el, "There was no comment found with: {$subject} as a subject");
        $el = $el
            ->find('xpath', '..')
            ->find('xpath', '..')
            ->find('xpath', '//div[contains(@class, "comment-body") and contains(., "'.$message.'")]');

        assertNotNull($el, "There was no comment found containing text: {$message}");
    }

    /**
     * @When /^I create a comment "([^"]*)" as an author "([^"]*)" and message:$/
     */
    function iCreateCommentAsAnAuthorAndMessage($subject, $author, PyStringNode $message) {
        $form = $this->find('css', 'form[name="comment"]');
        assertNotNull($form, "There was no comment form found on page.");

        $form->fillField('comment[subject]', $subject);
        $author && $form->fillField('comment[author]', $author);
        $form->fillField('comment[content]', trim((string)$message));

        $form->pressButton('Submit');
    }

    /**
     * @Then /^I should see an? "(error|success)" notification containing text "([^"]*)"$/
     */
    function iShouldSeeNotificationContainingText($type, $text) {
        $el = $this->find('xpath', '//div[contains(@class, "alert-'.$type.'") and contains(., "'.$text.'")]', 5);
        assertNotNull($el, "There was no [{$type}] notification found containing [{$text}] text.");
    }

    /**
     * @Then /^I should see an error message saying "([^"]*)"$/
     */
    function iShouldSeeAnErrorMessageSaying($text) {
        $el = $this->find('xpath', '//span[contains(@class, "help-inline") and contains(., "'.$text.'")]', 5);
        assertNotNull($el, "There was no error message found with [{$text}] text.");
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
