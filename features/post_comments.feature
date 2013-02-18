Feature: Blog post comments

  Blog application should provide
  A way to read and add comments for all blog posts

  Background:
    Given I have a post "My blog post" with body:
      """
        <p>a body of the blog post</p>
      """
    And I have a post "My blog post" comment "Some comment" with body:
      """
        some comment body
      """
    And I have a post "My blog post" comment "Hello" with body:
      """
        world
      """

  @javascript
  Scenario: Comments should be visible on blog post
    When I go to homepage
    Then I should see "My blog post" as one of blog post titles
    When I follow "My blog post"
    Then I should see "body of the blog post" as a post body
    And I should see a comment "Some comment" containing message "comment body"
    And I should see a comment "Hello" containing message "world"

  @javascript
  Scenario: Should be possible to add a new comment
    When I go to homepage
    Then I should see "My blog post" as one of blog post titles
    When I follow "My blog post"
    And I create a comment "My comment" as an author "Gedi Knight" and message:
      """
        This is my **comment** on [homepage](/)
      """
    Then I should see a "success" notification containing text "comment was added"
    And I should see a comment "My comment" containing message "This is my comment"

  @javascript
  Scenario: Should not allow to submit invalid comment
    When I go to homepage
    Then I should see "My blog post" as one of blog post titles
    When I follow "My blog post"
    And I create a comment "" as an author "Gedi Knight" and message:
      """
      """
    Then I should see an error message saying "Subject should be specified"
    And I should see an error message saying "Comment should have body"

