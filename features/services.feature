Feature: Lazy services

  As a functor user
  I should be able to store and fetch lazy services

  Scenario: Store and fetch the same service instance
    Given I have a "bookstore" service created
    When I get service "bookstore"
    Then on next fetch of service "bookstore" instance should be same
    And on next fetch of service "bookstore" instance should be same

  Scenario: Should not be able to store service under the same name or fetch undefined
    Given I have a "blog.service" service created
    Then I should not be able to create another service as "blog.service"
    And I should not be able to access undefined service like "undefined.service"

