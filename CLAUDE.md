# CLAUDE.md

This file provides guidance to Claude Code (claude.ai/code) when working with code in this repository.

## Plugin Overview

Atlas Returns for WooCommerce is a WordPress plugin for managing product returns and exchanges in WooCommerce stores. It features a freemium model with Freemius SDK integration, offering basic functionality for free and advanced features in Pro.

**Requirements:** PHP 7.4+, WordPress 6.0+, WooCommerce 7.0+

## Commands

### Development
```bash
npm run dev          # Start webpack dev server (wp-scripts)
npm run build        # Production build
npm run lint:js      # Lint JavaScript
npm run lint:css     # Lint CSS/SCSS
npm run build:pot    # Generate translation file
```

### Testing & Code Quality
```bash
composer test        # Run PHPUnit tests
composer phpcs       # Run PHP CodeSniffer (WordPress-Extra standard)
composer phpcbf      # Auto-fix PHPCS issues

# Run specific test file
vendor/bin/phpunit tests/Unit/CostCalculatorTest.php

# Run with filter
vendor/bin/phpunit --filter testMethodName
```

### Dependencies
```bash
composer install     # Install PHP dependencies
npm install          # Install Node dependencies
```

## Architecture

### Namespace & Autoloading
- **Free features:** `AtlasReturns\` namespace maps to `includes/`
- **Pro features:** `AtlasReturns\Pro\` namespace maps to `includes-pro/`
- Uses PSR-4 autoloading via Composer (falls back to manual autoloader if vendor not present)

### Core Classes

| Class | Purpose |
|-------|---------|
| `Plugin` | Singleton entry point, bootstraps all functionality |
| `Activator`/`Deactivator` | Plugin lifecycle hooks, database table creation |
| `Admin\Admin` | Admin menu pages, asset loading |
| `Admin\Settings` | Plugin settings registration |
| `Api\AjaxHandler` | All AJAX endpoints for return processing |
| `Core\CostCalculator` | Calculates return costs based on reason type |
| `Core\CalculationResult` | Value object for calculation results |
| `Core\ReturnRepository` | Database CRUD for `wp_atlr_returns` table |
| `Core\OrderCreator` | Creates replacement WooCommerce orders |
| `Core\CouponHandler` | Generates refund coupons for customers |

### Pro Features (includes-pro/)
- `ProLoader` - Loads when Pro is active
- `Analytics\Analytics` - Return statistics and reports
- `Analytics\DashboardWidget` - WP dashboard widget
- `Export\CsvExporter` - Export returns to CSV

### Database
Custom table `{prefix}atlr_returns` stores return records with fields for original/return order IDs, reason, products (JSON), costs, and coupon reference.

### AJAX Endpoints
All require `manage_atlas_returns` capability and `atlr_nonce` verification:
- `atlr_preview_order` - Get order details by ID or phone
- `atlr_calculate_return` - Calculate costs for return
- `atlr_create_return` - Process and create return order
- `atlr_get_history` - Paginated return history

### Return Reasons
- `customer_fault` - Customer pays shipping + COD fee (Free)
- `company_fault` - Company sent wrong product, special handling (Pro)
- `company_fault_no_special_admin` - Defective product, no pickup (Pro)

## Key Patterns

### Free vs Pro Feature Gating
```php
// Check Pro status anywhere
\AtlasReturns\Plugin::instance()->is_pro();

// Development mode override (atlas-returns.php:68)
define( 'ATLR_DEV_PRO', true ); // Simulates Pro for development
```

### Monthly Limit (Free Version)
Free version limited to 20 returns/month. Count stored in `atlr_monthly_returns_count` option, reset tracked via `atlr_monthly_returns_reset_date`.

### WooCommerce HPOS Compatibility
Plugin declares HPOS compatibility and uses `wc_get_orders()` with proper checks for High-Performance Order Storage.

## Testing

Tests are in `tests/` with WordPress function mocks in `bootstrap.php`. Test suites:
- `Unit` - Pure PHP unit tests with mocked WP functions
- `Integration` - (future) WordPress integration tests

## Assets

- Source files: `assets/src/js/` and `assets/src/scss/`
- Built files: `assets/dist/` (minified via wp-scripts)
- Chart.js loaded from CDN for analytics page

## Configuration Options

Stored in WordPress options table with `atlr_` prefix:
- `atlr_shipping_cost` - Shipping fee for customer fault (default: 2.00)
- `atlr_cod_fee` - COD fee for customer fault (default: 1.00)
- `atlr_coupon_validity_days` - Coupon expiry (default: 180)
- `atlr_special_handling_note` - Note for company fault orders
- `atlr_enable_email_notifications` - Send coupon emails (default: yes)
