# Abstract Form Maker

A flexible form maker package that allows you to define custom form groups for WordPress posts and taxonomies.

## Installation

You can install this package with Composer:

```bash
composer require digitalnodecom/larafields
```

After installation, you need to:

1. Publish the package assets and configuration:

```bash
wp acorn vendor:publish --tag="larafields"
```

2. Run the database migrations:

```bash
wp acorn migrate
```

3. Add the following hooks to your current theme's `functions.php` file.

```
use Livewire\Mechanisms\HandleRequests\HandleRequests;

add_filter('admin_enqueue_scripts', function () {
    echo Blade::render('@livewireStyles');
});

add_filter('admin_footer', function () {
    echo Blade::render('@livewireScripts');
});

add_action('init', function() {
    if (function_exists('app') && class_exists(Route::class)) {
        Route::post('/livewire/update', [HandleRequests::class, 'handleUpdate'])->name('livewire.update')->middleware('web');

        app('router')->getRoutes()->refreshNameLookups();
        app('router')->getRoutes()->refreshActionLookups();
    }
}, 20);
```

4. Clear the cached views

`wp acorn clear:views`

## Configuration

Form groups are defined in the `config/larafields.php` file. Each form group can be configured to display on specific post types or taxonomies.

### Basic Structure

```php
return [
    'forms' => [
        [
            'label'    => 'Your Field Group Name',
            'name'     => 'unique_field_group_name',
            'settings' => [
                'showInRestApi' => true,
                'storage'       => [
                    'type'     => 'json',
                    'location' => 'shared_table'
                ],
                'conditions'    => [
                    // Define where to display the form
                ]
            ],
            'fields'   => [
                // Your field definitions
            ]
        ]
    ]
];
```

### Display Conditions

You can display form groups on any Post Type, any Term, and/or create a custom Options page:

#### Post Type Display

This will display the form group on the specified post type's edit screen in the WordPress Admin dashboard.

```php
'conditions' => [
    [ 'postType' => 'product' ]
]
```

#### Taxonomy Display

This will display the form group on the specified taxonomy's term edit screen in the WordPress Admin dashboard.

```php
'conditions' => [
    [ 'taxonomy' => 'product_brand' ]
]
```

#### Options Page Display

This will create a new page in the WordPress Admin dashboard with the specified title and menu entry. The form group will be rendered on this custom options page.

```php
'conditions' => [
    [
        'page' => [
            'page_title' => 'Testing',
            'menu_title' => 'Testing',
            'slug' => 'testing',
            // 'parent' => 'parent_slug'  // Optional: Specify a parent slug to create this as a submenu page
        ]
    ]
]
```

#### Term Option Page Display

This will add an action link (alongside Edit, Quick Edit, Delete, View) on the taxonomy overview page. When clicked, it opens a custom page where the form group will be rendered, with the data being related to both the selected term and taxonomy.

```php
'conditions' => [
    'term_page' => [
        'taxonomy' => 'wcpv_product_vendors',
        'action_name' => 'Change Mappings',
        'page_title' => 'Brand Testing',
        'menu_title' => 'Brand Testing',
        'slug' => 'brand_testing'
    ]
]
```

#### User Profile Display

This will display the form group on the user's "Edit Profile" page in the WordPress Admin dashboard.

```php
'conditions' => [
    'user'
]
```

#### User Page Display

This will add an action link (alongside Edit, Delete, and other user actions) on the Users overview page. When clicked, it opens a custom page where the form group will be rendered for the selected user.

```php
'conditions' => [
    'user_page' => [
        'action_name' => 'Adjust Mappings',
        'page_title' => 'Vendor User Mappings',
        'menu_title' => 'Vendor User Mappings',
        'slug' => 'vendor_mappings_user'
    ]
]
```

## Available Field Types

### Text Field

Simple text input with optional character limit.

```php
[
    'type'           => 'text',
    'label'          => 'Input Label',
    'name'           => 'field_name',
    'defaultValue'   => 'Default text',
    'required'       => true,
    'characterLimit' => 50
]
```

### Date Field

Date picker input field.

```php
[
    'type'           => 'date',
    'label'          => 'Date Created',
    'name'           => 'date_created',
    'defaultValue'   => '',
    'required'       => true,
    'characterLimit' => 50
]
```

### DateTime Field

Date and time picker input field.

```php
[
    'type'           => 'datetime',
    'label'          => 'Event Date and Time',
    'name'           => 'event_datetime',
    'defaultValue'   => '',
    'required'       => true,
    'characterLimit' => 50
]
```

### Week Field

Week picker input field.

```php
[
    'type'           => 'week',
    'label'          => 'Week Selection',
    'name'           => 'selected_week',
    'defaultValue'   => '',
    'required'       => true,
    'characterLimit' => 50
]
```

### Month Field

Month picker input field.

```php
[
    'type'           => 'month',
    'label'          => 'Month Selection',
    'name'           => 'selected_month',
    'defaultValue'   => '',
    'required'       => true,
    'characterLimit' => 50
]
```

### Textarea Field

Multiline text input with optional character limit.

```php
[
    'type'           => 'textarea',
    'label'          => 'Textarea Label',
    'name'           => 'textarea_field',
    'defaultValue'   => 'Default multiline text',
    'required'       => false,
    'characterLimit' => 200
]
```

### Number Field

Numeric input with optional min/max values.

```php
[
    'type'         => 'number',
    'label'        => 'Number Input',
    'name'         => 'number_field',
    'defaultValue' => 10,
    'required'     => true,
    'minValue'     => 1,
    'maxValue'     => 100
]
```

### Multiselect Field

Dropdown field that allows selecting multiple options.

```php
[
    'type'         => 'multiselect',
    'label'        => 'Multiple Choice',
    'name'         => 'multiselect_field',
    'defaultValue' => '',
    'required'     => true,
    'options'      => [
        [
            'value' => 'option1',
            'label' => 'Option 1'
        ],
        [
            'value' => 'option2',
            'label' => 'Option 2'
        ]
    ],
    'custom_values' => false // Set to true to allow users to enter custom values not in the options list
]
```

When `custom_values` is set to `true`, users can enter and select values that are not predefined in the options list. This is useful when you want to allow for flexible input while still providing common options.

### Repeater Field

Group of fields that can be repeated multiple times.

```php
[
    'type'          => 'repeater',
    'label'         => 'Repeatable Group',
    'name'          => 'repeater_field',
    'subfields'     => [
        [
            'type'           => 'text',
            'label'          => 'Sub Field 1',
            'name'           => 'sub_field_1',
            'defaultValue'   => '',
            'required'       => true
        ],
        [
            'type'           => 'text',
            'label'          => 'Sub Field 2',
            'name'           => 'sub_field_2',
            'defaultValue'   => '',
            'required'       => true
        ]
    ]
]
```

## Adding Field Groups Programmatically

You can programmatically add new field groups using the `FormMaker::add_group()` method. This can be added to an action hook in your theme or plugin:

```php
add_action('init', function() {
    FormMaker::add_group([
        'label'    => 'CODE Field Group Term',
        'name'     => 'code_field_group_term',
        'settings' => [
            // settings
        ],
        'fields'   => [
            // fields
        ]
    ]);
});
```

The structure follows the same format as defined in the configuration file, allowing you to specify labels, settings, and fields for your new group.

## Extending Fields

You can modify or manipulate the existing fields using the `larafields_load_fields` WordPress filter. This filter provides access to the fields collection before it's processed:

```php
use Illuminate\Support\Collection;

add_filter('larafields_load_fields', function(Collection $fields){
    // Manipulate the $fields collection.
});
```

## Dashboard Menu Pages

You can add custom dashboard menu pages using the `larafields_load_pages` filter. This allows you to create additional admin pages for your application.

### Adding Dashboard Menu Pages

Use the `larafields_load_pages` filter to add new pages to the WordPress admin dashboard:

```php
add_filter('larafields_load_pages', function ($pages) {
    return [
        [
            'type' => 'page',
            'page_title' => 'Example Page',
            'menu_title' => 'Example Page',
            'slug' => 'page-example',
            // 'hide_from_submenu' => true // This prevents the parent page from appearing in the submenu
        ],
    ];
});
```

## API Documentation

The package provides REST API endpoints for querying and updating forms and their data.

### Query Endpoint

```
GET /larafields/forms
```

### Authentication

The API uses Basic Authentication with WordPress Application Passwords:

- Username: Your WordPress username
- Password: Generated Application Password key

### Request Parameters

The endpoint accepts the following query parameters:

| Parameter   | Required | Description                                                 |
| ----------- | -------- | ----------------------------------------------------------- |
| object_id   | No*      | The ID of the object (post, term, etc.)                     |
| object_name | No*      | The name of the object type                                 |
| field_key   | No*      | The key of the specific field to retrieve                   |

*At least one of these parameters must be provided.

### Field Validation

The API implements the following validation rules:
- `object_id`: Required if neither `object_name` nor `field_key` is provided
- `object_name`: Required if neither `object_id` nor `field_key` is provided
- `field_key`: Required if neither `object_id` nor `object_name` is provided

This means you must provide at least one of these three parameters in your request.

### Query Examples

#### Query by Object ID

```
GET /larafields/forms?object_id=123
```

This will return all form data associated with the object ID 123.

#### Query by Object Name

```
GET /larafields/forms?object_name=product
```

This will return all form data associated with the object type "product".

#### Query by Field Key

```
GET /larafields/forms?field_key=product_gender
```

This will return the specific field data for the field key "product_gender".

#### Combined Query

```
GET /larafields/forms?object_id=123&object_name=product
```

This will return form data that matches both the object ID 123 and object type "product".

### Update Endpoint

```
POST /larafields/forms
```

#### Authentication

The API uses Basic Authentication with WordPress Application Passwords (same as the Query Endpoint).

#### Request Body

The request body should be a JSON object with the following properties:

| Property    | Required | Description                             |
| ----------- | -------- | --------------------------------------- |
| field_key   | Yes      | The key of the field to update          |
| field_value | Yes      | The new value for the field             |
| object_id   | Yes      | The ID of the object (post, term, etc.) |
| object_name | Yes      | The name of the object type             |

#### Example Request

```json
{
  "field_key": "location",
  "field_value": "Europe",
  "object_id": "1000",
  "object_name": "product"
}
```

#### Response

A successful update will return a JSON response with status 'ok':

```json
{
  "status": "ok"
}
```

## Complete Examples

### Post Type Form Group

```php
[
    'label'    => 'Product Details',
    'name'     => 'product_details',
    'settings' => [
        'showInRestApi' => true,
        'storage'       => [
            'type'     => 'json',
            'location' => 'shared_table'
        ],
        'conditions'    => [
            [ 'postType' => 'product' ]
        ]
    ],
    'fields'   => [
        [
            'type'         => 'multiselect',
            'label'        => 'Product Gender',
            'name'         => 'product_gender',
            'defaultValue' => '',
            'required'     => true,
            'options'      => [
                [
                    'value' => 'men',
                    'label' => 'Men'
                ],
                [
                    'value' => 'women',
                    'label' => 'Women'
                ],
                [
                    'value' => 'unisex',
                    'label' => 'Unisex'
                ]
            ]
        ],
        [
            'type'           => 'textarea',
            'label'          => 'Product Description',
            'name'           => 'product_description',
            'defaultValue'   => '',
            'required'       => false,
            'characterLimit' => 200
        ]
    ]
]
```

### Taxonomy Form Group

```php
[
    'label'    => 'Brand Information',
    'name'     => 'brand_information',
    'settings' => [
        'showInRestApi' => true,
        'storage'       => [
            'type'     => 'json',
            'location' => 'shared_table'
        ],
        'conditions'    => [
            [ 'taxonomy' => 'product_brand' ]
        ]
    ],
    'fields'   => [
        [
            'type'          => 'repeater',
            'label'         => 'Brand Contacts',
            'name'          => 'brand_contacts',
            'subfields'     => [
                [
                    'type'           => 'text',
                    'label'          => 'Contact Name',
                    'name'           => 'contact_name',
                    'defaultValue'   => '',
                    'required'       => true
                ],
                [
                    'type'           => 'text',
                    'label'          => 'Contact Email',
                    'name'           => 'contact_email',
                    'defaultValue'   => '',
                    'required'       => true
                ]
            ]
        ],
        [
            'type'           => 'text',
            'label'          => 'Brand Website',
            'name'           => 'brand_website',
            'defaultValue'   => '',
            'required'       => true,
            'characterLimit' => 100
        ]
    ]
]
```
