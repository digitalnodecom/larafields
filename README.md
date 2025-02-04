# Abstract Form Builder

A flexible form builder package that allows you to define custom form groups for WordPress posts and taxonomies.

## Installation

You can install this package with Composer:

```bash
composer require brandsgateway/abstract-form-builder
```

After installation, you need to:

1. Publish the package assets and configuration:

```bash
wp acorn vendor:publish --tag="form-maker"
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

## Configuration

Form groups are defined in the `config/form-maker.php` file. Each form group can be configured to display on specific post types or taxonomies.

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
            'slug' => 'testing'
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
    ]
]
```

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

You can modify or manipulate the existing fields using the `dn_form_maker_load_fields` WordPress filter. This filter provides access to the fields collection before it's processed:

```php
use Illuminate\Support\Collection;

add_filter('dn_form_maker_load_fields', function(Collection $fields){
    // Manipulate the $fields collection.
});
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
