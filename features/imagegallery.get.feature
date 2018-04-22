Feature: Get image from database
  In order to use the application
  I need to be able to retrieve details about an existing image using the GET method

  Background: Reset image table before each scenario
    Given the image table is empty


  Scenario: Get 405 error on bad method usage
    When I send a "POST" request to "/admin/api/gallery/image/abcd"
    Then the response status code should be 405
    And the response should be in JSON
    And the header "Content-Type" should be equal to "application/problem+json"
    And the JSON node "status" should be equal to "error"
    And the JSON node "code" should be equal to "405"
    And the JSON node "type" should be equal to "Method Not Allowed"
    And the JSON node "reason" should be equal to 'No route found for "POST /admin/api/gallery/image/abcd": Method Not Allowed (Allow: GET, DELETE, PATCH)'

  Scenario: Retrieve an image with valid informations
    Given I load a predictable image in database and get it's id
    And I save it into "ImageID"
    When I add "Content-Type" header equal to "application/json"
    And I add "Accept" header equal to "application/json"
    And I send a "GET" request to "/admin/api/gallery/image/<<ImageID>>"
    Then the response status code should be 200
    And the response should be in JSON
    And the header "Content-Type" should be equal to "application/json"
    And the JSON node "status" should be equal to "success"
    And the JSON node "code" should be equal to "200"
    And the JSON node "request-method" should be equal to "GET"
    And the JSON node "results-for" should be equal to "<<ImageID>>"
    And the JSON node "results.gallery-image.title" should be equal to "TestImageTitle"
    And the JSON node "results.gallery-image.description" should be equal to "TestImageDescription"
    And the JSON node "results.gallery-image.file" should be equal to "test.png"

  Scenario: Get 404 error on not existing UUID
    When I send a "GET" request to "/admin/api/gallery/image/4903"
    Then the response status code should be 404
    And the response should be in JSON
    And the header "Content-Type" should be equal to "application/problem+json"
    And the JSON node "status" should be equal to "error"
    And the JSON node "code" should be equal to "404"
    And the JSON node "type" should be equal to "Not Found"
    And the JSON node "reason" should be equal to "The resource you have requested can't be found"