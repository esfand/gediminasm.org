Feature: Blog posts

  Blog application should provide
  A way to read blog posts

  @javascript
  Scenario: Blog post is visible on main page
    Given I have a post "My blog post" with body:
      """
        <p>a body of the blog post</p>
      """
    When I go to homepage
    Then I should see "My blog post" as one of blog post titles
     And a post "My blog post" should be published "3 hours ago"
    When I follow "My blog post"
    Then I should see "body of the blog post" as a post body

