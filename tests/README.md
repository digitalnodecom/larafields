# Larafields API Tests

This directory contains tests for the Larafields API endpoints. The tests are written using [PEST](https://pestphp.com/), a testing framework for PHP with a focus on simplicity.

## Test Coverage

The tests cover the following functionality:

### 1. Reading Existing Data

- Reading data by object_id
- Reading data by object_name
- Reading data by field_key
- Reading data with combined parameters
- Validation when no parameters are provided

### 2. Inserting New Data

- Successfully inserting new data
- Validation for required fields (field_key, field_value, object_id, object_name)
- Validation for JSON format in field_value

### 3. Field Type Validation

- Text field constraints (character limits)
- Number field constraints (numeric values, min/max)
- Multiselect field constraints (valid options)

### 4. Updating Existing Data

- Successfully updating existing data

## Running the Tests

To run the tests, you need to have PEST and GuzzleHttp installed. These packages are included as dev dependencies in the composer.json file.

```bash
# Install dependencies (including PEST and GuzzleHttp)
composer install

# Run the tests
composer test
```

If you encounter any errors, make sure to run:

```bash
composer dump-autoload
```

This will regenerate the autoload files and ensure that all namespaces are properly loaded.

### About the Test Implementation

The tests use GuzzleHttp's MockHandler to simulate HTTP requests and responses, allowing us to test the API without making actual HTTP requests. This approach has several advantages:

1. Tests run faster since they don't make actual HTTP requests
2. Tests are more reliable since they don't depend on external services
3. We can easily test error cases by mocking error responses

Each test follows this pattern:
1. Set up a mock response
2. Create a client with the mock handler
3. Make a request to the API
4. Assert that the response matches our expectations

## Test Environment

The tests use an in-memory SQLite database for testing, which is configured in the phpunit.xml file. This ensures that the tests don't affect your actual database.

## Authentication

The tests use Basic Authentication with WordPress Application Passwords. In the test environment, this is mocked with test credentials.

## Important Notes

1. The tests assume that the Larafields table exists in the database. The migration should be run before running the tests in a real environment.

2. The tests use a request body for both GET and POST requests, as specified in the API documentation.

3. Field validation tests assume certain field configurations. Make sure your test environment has the appropriate field configurations set up.
