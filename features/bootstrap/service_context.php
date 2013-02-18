<?php

use Behat\Behat\Context\BehatContext;
use Behat\Gherkin\Node\PyStringNode,
    Behat\Gherkin\Node\TableNode;
use Behat\Behat\Context\Step\Then,
    Behat\Behat\Context\Step\When,
    Behat\Behat\Context\Step\Given;

class ServiceContext extends BehatContext {

    private $splHashes = array();

    /**
     * @BeforeScenario
     */
    function cleanSplHashesBeforeScenario() {
        $this->splHashes = array();
    }

    /**
     * @Given /^I have a "([^"]*)" service created$/
     */
    function iHaveAServiceCreated($name) {
        service($name, function() use ($name) {
            $service = new stdClass;
            $service->name = $name;
            return $service;
        });
    }

    /**
     * @When /^I get service "([^"]*)"$/
     */
    function iGetService($name) {
        $service = service($name);
        assertEquals($name, $service->name);
        $this->splHashes[$name] = spl_object_hash($service);
    }

    /**
     * @Then /^on next fetch of service "([^"]*)" instance should be same$/
     */
    function onNextFetchOfService($name) {
        $service = service($name);
        assertEquals($name, $service->name, "Service '{$service->name}' does not match expected '$name'");
        assertSame($this->splHashes[$name], spl_object_hash($service), "Spl hashes of '$name' does not match");
    }

    /**
     * @Then /^I should not be able to create another service as "([^"]*)"$/
     */
    function iShouldNotBeAbleToCreateAnotherServiceAs($name) {
        try {
            $this->iHaveAServiceCreated($name);
        } catch (InvalidArgumentException $e) {
            return;
        }
        throw new Exception("Expected exception was not cought");
    }

    /**
     * @Then /^I should not be able to access undefined service like "([^"]*)"$/
     */
    function iShouldNotBeAbleToAccessUndefinedServiceLike($name) {
        try {
            $this->iGetService($name);
        } catch (InvalidArgumentException $e) {
            return;
        }
        throw new Exception("Service was found by the given name '$name'");
    }
}
