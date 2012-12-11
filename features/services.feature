Feature: Lazy services

  As a functor user
  I should be able to store and fetch lazy services

  Scenario: Store and fetch the same service instance
    Given I have a "db" service created
    When I get service "db"
    Then on next fetch of service "db" instance should be same
    And on next fetch of service "db" instance should be same

  Scenario: Should not be able to store service under the same name or fetch undefined
    Given I have a "http" service created
    Then I should not be able to create another service as "http"
    And I should not be able to access undefined service like "undefined"

