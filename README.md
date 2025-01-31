# Abstract Form Builder

A flexible form builder package that allows you to define custom form groups for WordPress posts and taxonomies.

## Installation

You can install this package with Composer:

```bash
composer require brandsgateway/abstract-form-builder
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
