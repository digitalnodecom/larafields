# Form Validation System

This document explains how to use the validation system implemented in the Larafields package.

## Overview

The validation system provides Laravel-based validation for form fields, including support for unique validation within repeater fields. Validation runs on form submission and displays errors below each field.

## Features

- **Laravel Validator Integration**: Uses Laravel's built-in validation system
- **Unique Within Repeater**: Validates that values are unique within a repeater field
- **Case-Insensitive Validation**: Uniqueness checks ignore case differences
- **Real-time Error Display**: Shows validation errors below each field
- **Required Field Validation**: Supports required field validation
- **Character Limit Validation**: Supports maximum character length validation

## Usage

### Basic Field Validation

Add validation rules to your field schema:

```php
[
    'type' => 'text',
    'label' => 'Field Label',
    'name' => 'field_name',
    'required' => true,
    'characterLimit' => 50,
]
```

### Unique Within Repeater Validation

For fields that should be unique within a repeater:

```php
[
    'type' => 'text',
    'label' => 'Vendor Category',
    'name' => 'vendor_category',
    'defaultValue' => '',
    'required' => true,
    'characterLimit' => 50,
    'validation' => [
        'unique_within_repeater' => true,
        'unique_message' => 'This vendor category already exists. Please choose a different name.',
    ],
]
```

## Validation Rules

### Built-in Rules

- `required`: Field must have a value
- `characterLimit`: Maximum number of characters (uses Laravel's `max` rule)

### Custom Rules

- `unique_within_repeater`: Ensures the value is unique within the current repeater field
  - Case-insensitive comparison
  - Ignores empty values
  - Trims whitespace before comparison

## Error Display

Validation errors are displayed:
- Below each field in red text
- With appropriate font sizes (text-sm for regular fields, text-xs for repeater fields)
- Only when validation fails

## Validation Flow

1. User clicks submit button
2. `validateForm()` method runs before submission
3. For each field with validation rules:
   - Laravel validation rules are built
   - Laravel validator runs
   - Errors are stored in `$validationErrors` property
4. If validation fails:
   - Submission is prevented
   - Error message is shown: "Please fix the validation errors before submitting."
   - Individual field errors are displayed below each field
5. If validation passes:
   - Normal form submission proceeds

## Technical Implementation

### Files Created/Modified

- `src/Rules/UniqueWithinRepeater.php`: Custom Laravel validation rule
- `src/Component/Traits/HasValidation.php`: Validation logic trait
- `src/Component/FormMakerComponent.php`: Updated to use validation
- `resources/views/livewire/form-maker.blade.php`: Updated to display errors

### Key Methods

- `validateForm()`: Main validation method
- `validateRepeaterField()`: Validates repeater fields
- `validateSingleField()`: Validates individual fields
- `buildValidationRules()`: Builds Laravel validation rules array
- `hasValidationError()`: Checks if field has errors
- `getValidationError()`: Gets error message for field

## Example Schema

```php
add_filter('larafields_load_forms', function ($forms) {
    return [
        [
            'label' => 'Category Mappings',
            'name' => 'vendor_mappings',
            'fields' => [
                [
                    'type' => 'repeater',
                    'label' => 'Category Mappings',
                    'name' => 'vendor_mappings',
                    'subfields' => [
                        [
                            'type' => 'text',
                            'label' => 'Vendor Category',
                            'name' => 'vendor_category',
                            'defaultValue' => '',
                            'required' => true,
                            'characterLimit' => 50,
                            'validation' => [
                                'unique_within_repeater' => true,
                                'unique_message' => 'This vendor category already exists. Please choose a different name.',
                            ],
                        ],
                        // ... other fields
                    ],
                ],
            ],
        ],
    ];
});
```

## Future Enhancements

The validation system is designed to be extensible. Future enhancements could include:

- Email format validation
- URL format validation
- Custom regex patterns
- Numeric range validation
- Cross-field validation
- Real-time validation (on field blur/change)
