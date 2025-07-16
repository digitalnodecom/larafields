# CLAUDE.md

This file provides guidance to Claude Code (claude.ai/code) when working with code in this repository.

## Project Overview

This is "Abstract Form Builder" (digitalnodecom/larafields) - a Laravel-powered form maker package for WordPress sites. It provides a flexible system for creating custom form groups that can be attached to WordPress posts, taxonomies, users, and custom admin pages.

## Development Commands

### Asset Building
- `npm run dev` - Watch for changes and rebuild both CSS and JavaScript
- `npm run dev:css` - Watch CSS files with Tailwind
- `npm run dev:js` - Watch JavaScript files with Webpack
- `npm run build` - Build production assets (CSS + JS)
- `npm run build:prod` - Build production assets (minified)
- `npm run build:dev` - Build development assets

### WordPress Integration
- `wp acorn clear` - Clear Acorn cache (required after installation)
- `wp acorn vendor:publish --tag="larafields"` - Publish package assets and configuration
- `wp acorn migrate` - Run database migrations
- `wp acorn clear:views` - Clear cached views

### Code Quality
- `vendor/bin/pint` - Run Laravel Pint for code formatting

## Architecture

### Core Components

**Main Package Class**: `src/Larafields.php`
- Central coordinator that loads forms and pages from config
- Manages WordPress hooks through WordPressHookService
- Provides static methods for adding form groups and retrieving field data

**Form Rendering**: `src/Component/FormMakerComponent.php`
- Livewire component that handles form rendering and submission
- Supports different contexts (post, taxonomy, user, custom pages)
- Includes pagination and search for repeater fields
- Handles file uploads and form validation

**Form Renderer Service**: `src/Services/FormRenderer.php`
- Handles Livewire form mounting and rendering
- Provides wrapper methods for different display contexts

### Key Traits
- `HasProcessesFields` - Field processing logic
- `HasRepeaterFields` - Repeater field functionality
- `HasValidation` - Form validation including unique-within-repeater rules

### Configuration
Forms are defined in `config/larafields.php` and support:
- Multiple field types (text, date, file, repeater, multiselect, etc.)
- Display conditions for post types, taxonomies, users, and custom pages
- Validation rules including unique constraints within repeaters
- REST API exposure settings

#### Field Configuration Options

**Repeater Fields**:
- `showSearch` (boolean, default: false) - Enable search functionality for nested repeaters. By default, only top-level repeaters display search fields to save screen space.

Example:
```php
[
    'type' => 'repeater',
    'label' => 'Contact Persons',
    'name' => 'contact_persons',
    'showSearch' => true,  // Enable search for this nested repeater
    'subfields' => [
        // ... field definitions
    ]
]
```

### Database
Uses `larafields` table with migrations in `database/migrations/`

### Frontend Assets
- **CSS**: Tailwind CSS compiled from `resources/styles/input.css`
- **JavaScript**: Webpack bundles `resources/js/app.js` with tom-select dependency
- **Views**: Blade templates in `resources/views/` for form components and layouts

## Key Development Patterns

### Adding Form Groups
Forms can be added via config or programmatically:
```php
FormMaker::add_group([
    'label' => 'Group Name',
    'name' => 'unique_name',
    'settings' => [...],
    'fields' => [...]
]);
```

### Retrieving Field Data
```php
FormMaker::get_field(?string $fieldKey, ?string $objectName, ?string $objectId)
```

### WordPress Hooks
- `larafields_load_forms` - Filter for modifying form configurations
- `larafields_load_pages` - Filter for adding custom admin pages

## Testing Context
This package runs within a WordPress environment using Roots/Acorn (Laravel components for WordPress). The namespace is `DigitalNode\Larafields`.