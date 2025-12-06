# Atlas Returns for WooCommerce

[![WordPress Plugin Version](https://img.shields.io/badge/version-2.0.0-blue.svg)](https://wordpress.org/plugins/atlas-returns-for-woocommerce/)
[![WordPress Tested](https://img.shields.io/badge/WordPress-6.7-green.svg)](https://wordpress.org/)
[![WooCommerce Tested](https://img.shields.io/badge/WooCommerce-9.4-purple.svg)](https://woocommerce.com/)
[![PHP Version](https://img.shields.io/badge/PHP-7.4%2B-777BB4.svg)](https://php.net/)
[![License](https://img.shields.io/badge/license-GPL--2.0%2B-orange.svg)](https://www.gnu.org/licenses/gpl-2.0.html)

Professional return and exchange management for WooCommerce stores. Streamline returns, calculate costs, and create replacement orders with ease.

## Features

### Free Version
- **Quick Order Lookup** - Find orders by ID or customer phone number
- **Automatic Cost Calculation** - Instantly calculate price differences
- **Replacement Order Creation** - Create new orders with one click
- **Coupon Generation** - Auto-generate credit coupons for customers
- **Return History** - Track all processed returns
- **20 Returns Per Month** - Perfect for small stores

### Pro Version
- **Unlimited Returns** - No monthly limits
- **All Return Reasons** - Customer fault, company fault, defective products
- **Analytics Dashboard** - Charts and insights on return patterns
- **CSV Export** - Export data for reporting
- **Priority Support**

## Requirements

- WordPress 6.0+
- WooCommerce 7.0+
- PHP 7.4+

## Installation

### From WordPress.org
1. Go to Plugins → Add New
2. Search for "Atlas Returns for WooCommerce"
3. Click Install Now, then Activate

### Manual Installation
1. Download the plugin zip file
2. Go to Plugins → Add New → Upload Plugin
3. Upload the zip file and activate

### From GitHub
```bash
cd wp-content/plugins/
git clone https://github.com/pluginatlas/atlas-returns-for-woocommerce.git
cd atlas-returns-for-woocommerce
composer install
npm install && npm run build
```

## Usage

1. Go to **Atlas Returns** in your WordPress admin
2. Enter an order ID or customer phone number
3. Select the return reason
4. Enter SKUs of products being returned (comma-separated)
5. Enter SKUs of replacement products
6. Click **Calculate** to preview costs
7. Click **Create Return Order** to process

## Configuration

Navigate to **Atlas Returns → Settings** to configure:

| Setting | Description | Default |
|---------|-------------|---------|
| Shipping Cost | Fee for customer-fault returns | €2.00 |
| COD Fee | Cash on delivery fee | €1.00 |
| Coupon Validity | Days until coupon expires | 180 |
| Default Payment | Payment method for new orders | COD |
| Email Notifications | Send coupon emails to customers | Yes |

## Return Reasons

| Reason | Who Pays | Fees Applied |
|--------|----------|--------------|
| Customer Fault | Customer | Shipping + COD |
| Company Fault (Special) | Store | None (pickup included) |
| Company Fault (Defective) | Store | None |

## Development

```bash
# Install dependencies
composer install
npm install

# Development build with watch
npm run dev

# Production build
npm run build

# Run PHP CodeSniffer
composer phpcs

# Run tests
composer test
```

## File Structure

```
atlas-returns-for-woocommerce/
├── assets/
│   ├── src/           # Source JS/SCSS
│   └── dist/          # Compiled assets
├── includes/          # Free version classes
│   ├── Admin/         # Admin pages & settings
│   ├── Api/           # AJAX handlers
│   ├── Core/          # Business logic
│   └── Traits/        # Shared traits
├── includes-pro/      # Pro version classes (requires license)
│   ├── Analytics/     # Analytics & reports
│   └── Export/        # CSV export
├── freemius/          # Freemius SDK
├── languages/         # Translation files
└── tests/             # PHPUnit tests
```

## Hooks & Filters

### Actions
```php
// After return order is created
do_action( 'atlr_return_order_created', $new_order, $original_order, $reason, $calculation );
```

### Filters
```php
// Modify available return reasons
add_filter( 'atlr_available_return_reasons', function( $reasons ) {
    return $reasons;
});

// Disable monthly limit
add_filter( 'atlr_monthly_limit_enabled', '__return_false' );
```

## Contributing

Contributions are welcome! Please:

1. Fork the repository
2. Create a feature branch (`git checkout -b feature/amazing-feature`)
3. Commit your changes (`git commit -m 'Add amazing feature'`)
4. Push to the branch (`git push origin feature/amazing-feature`)
5. Open a Pull Request

## Support

- **Documentation**: [pluginatlas.com/docs](https://pluginatlas.com/docs)
- **Support**: [pluginatlas.com/support](https://pluginatlas.com/support)
- **Issues**: [GitHub Issues](https://github.com/pluginatlas/atlas-returns-for-woocommerce/issues)

## License

This plugin is licensed under the GPL v2 or later.

---

Made with ❤️ by [PluginAtlas](https://pluginatlas.com)
