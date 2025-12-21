# CLAUDE.md

This file provides guidance to Claude Code (claude.ai/code) when working with code in this repository.

## Plugin Overview

Atlas Returns for WooCommerce is a WordPress plugin for managing product returns and exchanges in WooCommerce stores. It features a freemium model offering basic functionality for free and advanced features in Pro.

**Requirements:** PHP 7.4+, WordPress 6.0+, WooCommerce 7.0+

## Folder Structure

| Folder | Purpose | Git Tracked? |
|--------|---------|--------------|
| `atlas-returns-for-woocommerce/` | **Main development folder** - ALL development happens here | ✅ Yes |
| `atlas-returns-release/` | **Build output only** - stores ZIP files for WordPress.org upload | ❌ No (.gitignore) |

### Development Workflow
```
1. DEVELOP:    Edit code in atlas-returns-for-woocommerce/
2. BUILD ZIP:  Create ZIP without Freemius SDK (see commands below)
3. STORE:      ZIP goes to atlas-returns-release/
4. UPLOAD:     Submit ZIP to WordPress.org
```

**IMPORTANT:** Never edit files in `atlas-returns-release/`. It only contains generated ZIP files that get recreated for each release.

## Important Links

| Resource | URL |
|----------|-----|
| **GitHub Repository** | https://github.com/kostasmm/atlas-returns-for-woocommerce |
| **WordPress.org Plugin** | https://wordpress.org/plugins/atlas-returns-for-woocommerce/ (pending approval) |
| **Plugin Submission** | https://wordpress.org/plugins/developers/add/ |
| **Plugin Guidelines** | https://developer.wordpress.org/plugins/wordpress-org/detailed-plugin-guidelines/ |
| **Plugin Developer FAQ** | https://developer.wordpress.org/plugins/wordpress-org/plugin-developer-faq/ |
| **Plugin Check Tool** | https://wordpress.org/plugins/plugin-check/ |
| **Freemius Dashboard** | https://dashboard.freemius.com/ |
| **Freemius SDK Docs** | https://freemius.com/help/documentation/wordpress-sdk/ |

## WordPress.org Submission

### Version Management

| Version | Location | Purpose |
|---------|----------|---------|
| **WordPress.org** | `atlas-returns-release/` ZIP | Free version WITHOUT Freemius SDK |
| **Pro/Development** | Full plugin folder | Includes Freemius SDK for licensing |

### Creating WordPress.org ZIP

**IMPORTANT:** WordPress.org does NOT allow custom plugin updaters. The free version must NOT include the Freemius SDK folder.

```bash
# Create ZIP WITHOUT Freemius or Pro code for WordPress.org:
cd wp-content/plugins
rm -rf atlas-returns-temp
mkdir -p atlas-returns-temp/atlas-returns-for-woocommerce
cp -r atlas-returns-for-woocommerce/assets \
      atlas-returns-for-woocommerce/includes \
      atlas-returns-for-woocommerce/languages \
      atlas-returns-temp/atlas-returns-for-woocommerce/
cp atlas-returns-for-woocommerce/*.php \
   atlas-returns-for-woocommerce/*.txt \
   atlas-returns-temp/atlas-returns-for-woocommerce/
# Create ZIP
cd atlas-returns-temp
zip -r ../atlas-returns-release/atlas-returns-for-woocommerce.zip atlas-returns-for-woocommerce
cd .. && rm -rf atlas-returns-temp
```

### Files to EXCLUDE from WordPress.org ZIP
- `freemius/` - Plugin updater not allowed
- `includes-pro/` - Pro-only code (violates trialware policy)
- `vendor/` - Dev dependencies
- `node_modules/` - Node packages
- `tests/` - Unit tests
- `.git/` - Version control
- `CLAUDE.md` - Development docs
- `composer.lock`, `package-lock.json`
- `.phpcs.xml.dist`

### Checklist Before Submission
- [ ] Update `readme.txt` → `Tested up to:` to latest WordPress version
- [ ] Update `readme.txt` → `Stable tag:` matches plugin version
- [ ] Update `atlas-returns-for-woocommerce.php` → `Version:` header
- [ ] Remove Freemius SDK folder from ZIP
- [ ] Remove includes-pro/ folder from ZIP (Pro code not allowed)
- [ ] Run Plugin Check tool locally
- [ ] Test plugin works without Freemius (uses mock object)

### Common WordPress.org Rejection Reasons
1. **Plugin Updater detected** - Remove Freemius SDK from free version
2. **Outdated Tested up to** - Must be current WordPress version (6.9+)
3. **Unescaped output** - Use `esc_html()`, `esc_attr()`, etc.
4. **Missing nonce verification** - Add `wp_verify_nonce()` or `check_ajax_referer()`
5. **Unsanitized input** - Use `sanitize_text_field()`, `absint()`, etc.

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
composer phpcs       # Run PHP CodeSniffer (uses .phpcs.xml.dist)
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

## Freemius Integration

### How It Works
- **With Freemius SDK:** Full licensing, Pro detection via `atlr_fs()->is_paying()`
- **Without Freemius SDK:** Mock object in `freemius-init.php` returns free plan

### Mock Object Behavior (WordPress.org version)
When Freemius SDK is not present, the mock object:
- `is_paying()` returns `false`
- `is_trial()` returns `false`
- `is_free_plan()` returns `true`
- All free features work normally

### Development Mode
```php
// In atlas-returns-for-woocommerce.php - simulate Pro for testing
define( 'ATLR_DEV_PRO', true );
```

### Freemius Credentials (for Pro version)
- **Plugin ID:** 22170
- **Slug:** atlas-returns-for-woocommerce
- **Public Key:** pk_4cb8f2227552c9730d7488db97809

## Key Patterns

### Free vs Pro Feature Gating
```php
// Check Pro status anywhere
\AtlasReturns\Plugin::instance()->is_pro();

// Check available reasons
\AtlasReturns\Plugin::instance()->get_available_reasons();

// Check if can create more returns
\AtlasReturns\Plugin::instance()->can_create_return();
```

### Monthly Limit (Free Version)
Free version limited to 20 returns/month. Count tracked in database table, reset monthly.

### WooCommerce HPOS Compatibility
Plugin declares HPOS compatibility and uses `wc_get_orders()` with proper checks for High-Performance Order Storage.

## PHPCS Configuration

The `.phpcs.xml.dist` file excludes:
- `freemius/` - Third-party SDK
- `vendor/` - Composer dependencies
- `node_modules/` - Node packages
- `assets/dist/` - Built files
- `tests/` - Test files

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

## Release Checklist

### For WordPress.org Updates
1. Update version in `atlas-returns-for-woocommerce.php` header
2. Update `Stable tag` in `readme.txt`
3. Update `Tested up to` in `readme.txt` if WordPress version changed
4. Add changelog entry in `readme.txt`
5. Create ZIP without Freemius SDK
6. Test with Plugin Check tool
7. Upload via SVN to WordPress.org

### For Pro Version Updates
1. Same as above, but include Freemius SDK
2. Deploy via Freemius dashboard
3. Update on your own distribution channel
