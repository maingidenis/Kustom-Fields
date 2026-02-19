# Kustom Fields

A lightweight WooCommerce plugin that conditionally adds **Company Name** and **Role / Position** fields to the billing section at checkout — appearing **before the address fields** — when the cart contains products from a specified category.

Supports both the **WooCommerce Blocks** checkout and the **Classic** checkout template.

## Features

- Registers fields via the WooCommerce Blocks API (`woocommerce_register_additional_checkout_field`) when available
- Falls back to classic `woocommerce_checkout_fields` filter
- Fields are shown/hidden dynamically via AJAX based on cart contents
- Server-side validation ensures fields are required when triggered
- Field values are saved to order meta
- Category slug is configurable via a single constant

## Installation

1. Upload the `kustom-fields` folder to `/wp-content/plugins/`
2. Activate the plugin through **Plugins** in WordPress admin
3. Open `kustom-fields.php` and update `KUSTOM_FIELDS_CATEGORY` to match your product category slug

## Configuration

In `kustom-fields.php`, change the category slug:

```php
define( 'KUSTOM_FIELDS_CATEGORY', 'education-provider' ); // your-category-slug
```

## File Structure

```
wp-content/plugins/kustom-fields/
├── kustom-fields.php   # Main plugin file
└── kustom-fields.js    # Frontend toggle script
```

## Requirements

- WordPress 6.0+
- WooCommerce 7.0+
- PHP 7.4+

## Version

1.2.0
