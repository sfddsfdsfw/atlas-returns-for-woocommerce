# Freemius Integration Guide

This document explains how to complete the Freemius integration for Atlas Returns for WooCommerce.

## Prerequisites

- Freemius account (create at https://freemius.com)
- Your plugin registered in Freemius dashboard

## Step 1: Register Your Plugin on Freemius

1. Go to https://dashboard.freemius.com/
2. Click "Add New Plugin"
3. Fill in the details:
   - **Plugin Name:** Atlas Returns for WooCommerce
   - **Slug:** atlas-returns-for-woocommerce
   - **Version:** 2.0.0
   - **Type:** Plugin
   - **WP Compatible:** Yes
   - **Has Free Version:** Yes
   - **Has Premium Version:** Yes

## Step 2: Get Your Credentials

After registering, note down:
- **Plugin ID:** (e.g., `12345`)
- **Public Key:** (e.g., `pk_a1b2c3d4e5f6...`)
- **Secret Key:** (keep this secure, not used in code)

## Step 3: Download the Freemius SDK

1. In Freemius Dashboard, go to **Settings > SDK**
2. Download the latest SDK
3. Extract the contents
4. Copy the `freemius` folder to your plugin root:
   ```
   atlas-returns-for-woocommerce/
   ├── freemius/           <-- Add this folder
   │   ├── start.php
   │   ├── includes/
   │   └── ...
   ├── atlas-returns.php
   └── ...
   ```

## Step 4: Update the Configuration

Edit `includes/freemius-init.php` and replace the placeholder values:

```php
$atlr_fs = fs_dynamic_init( array(
    'id'                  => '12345',                    // Your Plugin ID
    'slug'                => 'atlas-returns-for-woocommerce',
    'type'                => 'plugin',
    'public_key'          => 'pk_YOUR_PUBLIC_KEY_HERE',  // Your Public Key
    'is_premium'          => false,
    'premium_suffix'      => 'Pro',
    'has_premium_version' => true,
    'has_addons'          => false,
    'has_paid_plans'      => true,
    'menu'                => array(
        'slug'    => 'atlas-returns',
        'contact' => false,
        'support' => false,
        'parent'  => array(
            'slug' => 'woocommerce',
        ),
    ),
    'is_live'             => true,
) );
```

## Step 5: Configure Pricing Plans in Freemius

1. Go to **Plans** in Freemius Dashboard
2. Create two plans:

### Free Plan
- **Name:** Free
- **Price:** $0
- **Features:**
  - 20 returns per month
  - Customer fault returns only
  - Basic cost calculation
  - Replacement order creation
  - Coupon generation

### Pro Plan
- **Name:** Pro
- **Price:** $49/year (suggested)
- **Features:**
  - Unlimited returns
  - All return reasons
  - Analytics dashboard
  - CSV export
  - Priority support

## Step 6: Configure Checkout

1. Go to **Settings > Checkout** in Freemius
2. Connect your payment gateway (Stripe/PayPal)
3. Configure:
   - Currencies accepted
   - VAT handling
   - Refund policy
   - Terms of service URL

## Step 7: Test the Integration

1. Activate the plugin on a test site
2. Verify:
   - [ ] Free version loads correctly
   - [ ] "Upgrade to Pro" links work
   - [ ] Analytics page shows Pro locked for free users
   - [ ] Pricing page displays correctly
3. Test checkout:
   - [ ] Test purchase with Freemius sandbox mode
   - [ ] Verify license activation
   - [ ] Verify Pro features unlock

## Step 8: Submit to WordPress.org

Before submitting to WordPress.org, ensure:

1. Remove the `freemius/` folder from the free version (Freemius auto-includes it)
2. Or use Freemius's WordPress.org deployment feature

### Using Freemius WordPress.org Deployment

1. Go to **Distribution > WordPress.org** in Freemius
2. Connect your WordPress.org account
3. Freemius will automatically:
   - Strip Pro-only code from free version
   - Add SDK to the free version
   - Deploy updates to WordPress.org

## Development Mode

During development, you can simulate Pro features without Freemius SDK:

1. In `atlas-returns.php`, find:
   ```php
   define( 'ATLR_DEV_PRO', false );
   ```

2. Change to:
   ```php
   define( 'ATLR_DEV_PRO', true );
   ```

This enables all Pro features without requiring a license.

**Remember to set back to `false` before production!**

## File Structure After Setup

```
atlas-returns-for-woocommerce/
├── atlas-returns.php
├── freemius/                    <-- Freemius SDK
│   ├── start.php
│   ├── includes/
│   │   ├── class-fs-plugin.php
│   │   └── ...
│   └── templates/
├── includes/
│   ├── freemius-init.php        <-- Your configuration
│   ├── Plugin.php
│   └── ...
└── ...
```

## Troubleshooting

### SDK not loading
- Ensure `freemius/start.php` exists
- Check file permissions

### Upgrade links not working
- Verify your public key is correct
- Check Freemius dashboard for errors

### Pro features not unlocking
- Clear any caching plugins
- Check license status in Freemius dashboard
- Try deactivating and reactivating the plugin

## Useful Links

- [Freemius Dashboard](https://dashboard.freemius.com/)
- [Freemius Documentation](https://freemius.com/help/documentation/)
- [Freemius SDK on GitHub](https://github.com/Freemius/wordpress-sdk)
- [WordPress.org Plugin Guidelines](https://developer.wordpress.org/plugins/wordpress-org/detailed-plugin-guidelines/)

## Support

For Freemius-specific issues:
- Email: support@freemius.com
- Documentation: https://freemius.com/help/

For Atlas Returns issues:
- GitHub: https://github.com/pluginatlas/atlas-returns
- Email: support@pluginatlas.com
