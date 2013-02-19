Feature: Contact message

  Blog application should provide
  A way to send a contact message to an author

  @javascript
  Scenario: I should be able to send personal message to an author
    When I go to "/contact"
    And I send a message from "Gedi knight" email "gedi@gediminasm.org" containing text:
      """
        Hello, please give some love back
      """
    Then I should see a "success" notification containing text "Hope I can answer to you soon ;)"
    And I should have a message "please give some love back" from "Gedi knight" in blog database
    And there should be 1 email sent to an author

  @javascript
  Scenario: Contact message should be validated
    When I go to "/contact"
    And I send a message from "" email "" containing text:
      """
      """
    Then I should see an error message saying "Field cannot be empty"
    And I should see an error message saying "Email must be entered"
    And I should see an error message saying "Message cannot be empty"
    When I press "Send"
    Then I still should see an error message saying "Field cannot be empty"

