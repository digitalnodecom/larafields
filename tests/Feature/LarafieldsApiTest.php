<?php

use GuzzleHttp\Client;
use GuzzleHttp\Handler\MockHandler;
use GuzzleHttp\HandlerStack;
use GuzzleHttp\Psr7\Response;

it('can read existing data by object_id', function () {
    // Mock a successful response
    $mock = new MockHandler([
        new Response(200, [], json_encode([
            [
                'object_id' => '123',
                'object_name' => 'product',
                'field_key' => 'product_gender',
                'created_at' => '2023-01-01 00:00:00',
                'updated_at' => '2023-01-01 00:00:00',
                'data' => ['men', 'women'],
            ],
            [
                'object_id' => '123',
                'object_name' => 'product',
                'field_key' => 'product_description',
                'created_at' => '2023-01-01 00:00:00',
                'updated_at' => '2023-01-01 00:00:00',
                'data' => 'Test product description',
            ],
        ])),
    ]);

    $handlerStack = HandlerStack::create($mock);
    $client = new Client(['handler' => $handlerStack]);

    // Make the request
    $response = $client->request('GET', '/larafields/forms', [
        'headers' => [
            'Content-Type' => 'application/json',
            'Accept' => 'application/json',
            'Authorization' => 'Basic '.base64_encode('test_user:test_app_password'),
        ],
        'json' => [
            'object_id' => '123',
        ],
    ]);

    // Assert response
    $this->assertEquals(200, $response->getStatusCode());
    $responseData = json_decode($response->getBody(), true);
    $this->assertCount(2, $responseData);
    $this->assertEquals('123', $responseData[0]['object_id']);
    $this->assertEquals('product', $responseData[0]['object_name']);
});

it('can read existing data by object_name', function () {
    // Mock a successful response
    $mock = new MockHandler([
        new Response(200, [], json_encode([
            [
                'object_id' => '123',
                'object_name' => 'product',
                'field_key' => 'product_gender',
                'created_at' => '2023-01-01 00:00:00',
                'updated_at' => '2023-01-01 00:00:00',
                'data' => ['men', 'women'],
            ],
            [
                'object_id' => '123',
                'object_name' => 'product',
                'field_key' => 'product_description',
                'created_at' => '2023-01-01 00:00:00',
                'updated_at' => '2023-01-01 00:00:00',
                'data' => 'Test product description',
            ],
        ])),
    ]);

    $handlerStack = HandlerStack::create($mock);
    $client = new Client(['handler' => $handlerStack]);

    // Make the request
    $response = $client->request('GET', '/larafields/forms', [
        'headers' => [
            'Content-Type' => 'application/json',
            'Accept' => 'application/json',
            'Authorization' => 'Basic '.base64_encode('test_user:test_app_password'),
        ],
        'json' => [
            'object_name' => 'product',
        ],
    ]);

    // Assert response
    $this->assertEquals(200, $response->getStatusCode());
    $responseData = json_decode($response->getBody(), true);
    $this->assertCount(2, $responseData);
});

it('can read existing data by field_key', function () {
    // Mock a successful response
    $mock = new MockHandler([
        new Response(200, [], json_encode([
            'object_id' => '123',
            'object_name' => 'product',
            'field_key' => 'product_gender',
            'created_at' => '2023-01-01 00:00:00',
            'updated_at' => '2023-01-01 00:00:00',
            'data' => ['men', 'women'],
        ])),
    ]);

    $handlerStack = HandlerStack::create($mock);
    $client = new Client(['handler' => $handlerStack]);

    // Make the request
    $response = $client->request('GET', '/larafields/forms', [
        'headers' => [
            'Content-Type' => 'application/json',
            'Accept' => 'application/json',
            'Authorization' => 'Basic '.base64_encode('test_user:test_app_password'),
        ],
        'json' => [
            'field_key' => 'product_gender',
        ],
    ]);

    // Assert response
    $this->assertEquals(200, $response->getStatusCode());
    $responseData = json_decode($response->getBody(), true);
    $this->assertEquals('123', $responseData['object_id']);
    $this->assertEquals('product', $responseData['object_name']);
    $this->assertEquals('product_gender', $responseData['field_key']);
    $this->assertEquals(['men', 'women'], $responseData['data']);
});

it('can read existing data with combined parameters', function () {
    // Mock a successful response
    $mock = new MockHandler([
        new Response(200, [], json_encode([
            [
                'object_id' => '123',
                'object_name' => 'product',
                'field_key' => 'product_gender',
                'created_at' => '2023-01-01 00:00:00',
                'updated_at' => '2023-01-01 00:00:00',
                'data' => ['men', 'women'],
            ],
            [
                'object_id' => '123',
                'object_name' => 'product',
                'field_key' => 'product_description',
                'created_at' => '2023-01-01 00:00:00',
                'updated_at' => '2023-01-01 00:00:00',
                'data' => 'Test product description',
            ],
        ])),
    ]);

    $handlerStack = HandlerStack::create($mock);
    $client = new Client(['handler' => $handlerStack]);

    // Make the request
    $response = $client->request('GET', '/larafields/forms', [
        'headers' => [
            'Content-Type' => 'application/json',
            'Accept' => 'application/json',
            'Authorization' => 'Basic '.base64_encode('test_user:test_app_password'),
        ],
        'json' => [
            'object_id' => '123',
            'object_name' => 'product',
        ],
    ]);

    // Assert response
    $this->assertEquals(200, $response->getStatusCode());
    $responseData = json_decode($response->getBody(), true);
    $this->assertCount(2, $responseData);
});

it('fails when no parameters are provided', function () {
    // Mock an error response
    $mock = new MockHandler([
        new Response(422, [], json_encode([
            'message' => 'The given data was invalid.',
            'errors' => [
                'object_id' => ['The object id field is required when none of object name / field key are present.'],
                'object_name' => ['The object name field is required when none of object id / field key are present.'],
                'field_key' => ['The field key field is required when none of object id / object name are present.'],
            ],
        ])),
    ]);

    $handlerStack = HandlerStack::create($mock);
    $client = new Client(['handler' => $handlerStack, 'http_errors' => false]);

    // Make the request
    $response = $client->request('GET', '/larafields/forms', [
        'headers' => [
            'Content-Type' => 'application/json',
            'Accept' => 'application/json',
            'Authorization' => 'Basic '.base64_encode('test_user:test_app_password'),
        ],
        'json' => [],
    ]);

    // Assert response
    $this->assertEquals(422, $response->getStatusCode());
});

it('can insert new data', function () {
    // Mock a successful response
    $mock = new MockHandler([
        new Response(200, [], json_encode([
            'status' => 'ok',
        ])),
    ]);

    $handlerStack = HandlerStack::create($mock);
    $client = new Client(['handler' => $handlerStack]);

    // Make the request
    $response = $client->request('POST', '/larafields/forms', [
        'headers' => [
            'Content-Type' => 'application/json',
            'Accept' => 'application/json',
            'Authorization' => 'Basic '.base64_encode('test_user:test_app_password'),
        ],
        'json' => [
            'field_key' => 'product_color',
            'field_value' => json_encode(['red', 'blue']),
            'object_id' => '456',
            'object_name' => 'product',
        ],
    ]);

    // Assert response
    $this->assertEquals(200, $response->getStatusCode());
    $responseData = json_decode($response->getBody(), true);
    $this->assertEquals('ok', $responseData['status']);
});

it('validates required field_key when inserting', function () {
    // Mock an error response
    $mock = new MockHandler([
        new Response(422, [], json_encode([
            'status' => 'error',
            'message' => 'The given data was invalid.',
            'errors' => [
                'field_key' => ['The field key field is required.'],
            ],
        ])),
    ]);

    $handlerStack = HandlerStack::create($mock);
    $client = new Client(['handler' => $handlerStack, 'http_errors' => false]);

    // Make the request
    $response = $client->request('POST', '/larafields/forms', [
        'headers' => [
            'Content-Type' => 'application/json',
            'Accept' => 'application/json',
            'Authorization' => 'Basic '.base64_encode('test_user:test_app_password'),
        ],
        'json' => [
            'field_value' => json_encode(['red', 'blue']),
            'object_id' => '456',
            'object_name' => 'product',
        ],
    ]);

    // Assert response
    $this->assertEquals(422, $response->getStatusCode());
});

it('validates required field_value when inserting', function () {
    // Mock an error response
    $mock = new MockHandler([
        new Response(422, [], json_encode([
            'status' => 'error',
            'message' => 'The given data was invalid.',
            'errors' => [
                'field_value' => ['The field value field is required.'],
            ],
        ])),
    ]);

    $handlerStack = HandlerStack::create($mock);
    $client = new Client(['handler' => $handlerStack, 'http_errors' => false]);

    // Make the request
    $response = $client->request('POST', '/larafields/forms', [
        'headers' => [
            'Content-Type' => 'application/json',
            'Accept' => 'application/json',
            'Authorization' => 'Basic '.base64_encode('test_user:test_app_password'),
        ],
        'json' => [
            'field_key' => 'product_color',
            'object_id' => '456',
            'object_name' => 'product',
        ],
    ]);

    // Assert response
    $this->assertEquals(422, $response->getStatusCode());
});

it('validates required object_id when inserting', function () {
    // Mock an error response
    $mock = new MockHandler([
        new Response(422, [], json_encode([
            'status' => 'error',
            'message' => 'The given data was invalid.',
            'errors' => [
                'object_id' => ['The object id field is required.'],
            ],
        ])),
    ]);

    $handlerStack = HandlerStack::create($mock);
    $client = new Client(['handler' => $handlerStack, 'http_errors' => false]);

    // Make the request
    $response = $client->request('POST', '/larafields/forms', [
        'headers' => [
            'Content-Type' => 'application/json',
            'Accept' => 'application/json',
            'Authorization' => 'Basic '.base64_encode('test_user:test_app_password'),
        ],
        'json' => [
            'field_key' => 'product_color',
            'field_value' => json_encode(['red', 'blue']),
            'object_name' => 'product',
        ],
    ]);

    // Assert response
    $this->assertEquals(422, $response->getStatusCode());
});

it('validates required object_name when inserting', function () {
    // Mock an error response
    $mock = new MockHandler([
        new Response(422, [], json_encode([
            'status' => 'error',
            'message' => 'The given data was invalid.',
            'errors' => [
                'object_name' => ['The object name field is required.'],
            ],
        ])),
    ]);

    $handlerStack = HandlerStack::create($mock);
    $client = new Client(['handler' => $handlerStack, 'http_errors' => false]);

    // Make the request
    $response = $client->request('POST', '/larafields/forms', [
        'headers' => [
            'Content-Type' => 'application/json',
            'Accept' => 'application/json',
            'Authorization' => 'Basic '.base64_encode('test_user:test_app_password'),
        ],
        'json' => [
            'field_key' => 'product_color',
            'field_value' => json_encode(['red', 'blue']),
            'object_id' => '456',
        ],
    ]);

    // Assert response
    $this->assertEquals(422, $response->getStatusCode());
});

it('validates field_value contains valid JSON', function () {
    // Mock an error response
    $mock = new MockHandler([
        new Response(422, [], json_encode([
            'status' => 'error',
            'message' => 'Invalid JSON in field_value: Syntax error',
        ])),
    ]);

    $handlerStack = HandlerStack::create($mock);
    $client = new Client(['handler' => $handlerStack, 'http_errors' => false]);

    // Make the request
    $response = $client->request('POST', '/larafields/forms', [
        'headers' => [
            'Content-Type' => 'application/json',
            'Accept' => 'application/json',
            'Authorization' => 'Basic '.base64_encode('test_user:test_app_password'),
        ],
        'json' => [
            'field_key' => 'product_color',
            'field_value' => '{invalid json',
            'object_id' => '456',
            'object_name' => 'product',
        ],
    ]);

    // Assert response
    $this->assertEquals(422, $response->getStatusCode());
    $responseData = json_decode($response->getBody(), true);
    $this->assertEquals('error', $responseData['status']);
    $this->assertEquals('Invalid JSON in field_value: Syntax error', $responseData['message']);
});

it('validates text field type constraints', function () {
    // Mock an error response
    $mock = new MockHandler([
        new Response(422, [], json_encode([
            'status' => 'error',
            'message' => 'Schema validation failed',
            'errors' => [
                'Field \'product_description\' exceeds character limit of 200',
            ],
        ])),
    ]);

    $handlerStack = HandlerStack::create($mock);
    $client = new Client(['handler' => $handlerStack, 'http_errors' => false]);

    // Make the request
    $longText = str_repeat('a', 201); // Assuming character limit is 200
    $response = $client->request('POST', '/larafields/forms', [
        'headers' => [
            'Content-Type' => 'application/json',
            'Accept' => 'application/json',
            'Authorization' => 'Basic '.base64_encode('test_user:test_app_password'),
        ],
        'json' => [
            'field_key' => 'product_description',
            'field_value' => json_encode($longText),
            'object_id' => '456',
            'object_name' => 'product',
        ],
    ]);

    // Assert response
    $this->assertEquals(422, $response->getStatusCode());
    $responseData = json_decode($response->getBody(), true);
    $this->assertEquals('error', $responseData['status']);
    $this->assertEquals('Schema validation failed', $responseData['message']);
});

it('validates number field type constraints', function () {
    // Mock an error response
    $mock = new MockHandler([
        new Response(422, [], json_encode([
            'status' => 'error',
            'message' => 'Schema validation failed',
            'errors' => [
                'Field \'product_price\' must be a number',
            ],
        ])),
    ]);

    $handlerStack = HandlerStack::create($mock);
    $client = new Client(['handler' => $handlerStack, 'http_errors' => false]);

    // Make the request
    $response = $client->request('POST', '/larafields/forms', [
        'headers' => [
            'Content-Type' => 'application/json',
            'Accept' => 'application/json',
            'Authorization' => 'Basic '.base64_encode('test_user:test_app_password'),
        ],
        'json' => [
            'field_key' => 'product_price',
            'field_value' => json_encode('not a number'),
            'object_id' => '456',
            'object_name' => 'product',
        ],
    ]);

    // Assert response
    $this->assertEquals(422, $response->getStatusCode());
    $responseData = json_decode($response->getBody(), true);
    $this->assertEquals('error', $responseData['status']);
    $this->assertEquals('Schema validation failed', $responseData['message']);
});

it('validates multiselect field type constraints', function () {
    // Mock an error response
    $mock = new MockHandler([
        new Response(422, [], json_encode([
            'status' => 'error',
            'message' => 'Schema validation failed',
            'errors' => [
                'Field \'product_gender\' contains invalid value \'invalid_option\'. Allowed values: men, women, unisex',
            ],
        ])),
    ]);

    $handlerStack = HandlerStack::create($mock);
    $client = new Client(['handler' => $handlerStack, 'http_errors' => false]);

    // Make the request
    $response = $client->request('POST', '/larafields/forms', [
        'headers' => [
            'Content-Type' => 'application/json',
            'Accept' => 'application/json',
            'Authorization' => 'Basic '.base64_encode('test_user:test_app_password'),
        ],
        'json' => [
            'field_key' => 'product_gender',
            'field_value' => json_encode(['invalid_option']),
            'object_id' => '456',
            'object_name' => 'product',
        ],
    ]);

    // Assert response
    $this->assertEquals(422, $response->getStatusCode());
    $responseData = json_decode($response->getBody(), true);
    $this->assertEquals('error', $responseData['status']);
    $this->assertEquals('Schema validation failed', $responseData['message']);
});

it('can update existing data', function () {
    // Mock a successful response
    $mock = new MockHandler([
        new Response(200, [], json_encode([
            'status' => 'ok',
        ])),
    ]);

    $handlerStack = HandlerStack::create($mock);
    $client = new Client(['handler' => $handlerStack]);

    // Make the request
    $response = $client->request('POST', '/larafields/forms', [
        'headers' => [
            'Content-Type' => 'application/json',
            'Accept' => 'application/json',
            'Authorization' => 'Basic '.base64_encode('test_user:test_app_password'),
        ],
        'json' => [
            'field_key' => 'product_gender',
            'field_value' => json_encode(['men', 'women', 'unisex']),
            'object_id' => '123',
            'object_name' => 'product',
        ],
    ]);

    // Assert response
    $this->assertEquals(200, $response->getStatusCode());
    $responseData = json_decode($response->getBody(), true);
    $this->assertEquals('ok', $responseData['status']);
});
