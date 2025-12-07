=== Atlas Returns for WooCommerce ===
Contributors: pluginatlas
Tags: woocommerce, returns, refunds, exchange, rma, order management
Requires at least: 6.0
Tested up to: 6.9
Requires PHP: 7.4
Stable tag: 2.0.0
License: GPLv2 or later
License URI: https://www.gnu.org/licenses/gpl-2.0.html

Professional return and exchange management for WooCommerce stores. Process returns, calculate costs, and create replacement orders efficiently.

== Description ==

**Atlas Returns for WooCommerce** is a powerful plugin that streamlines the return and exchange process for your WooCommerce store. Save time and reduce errors with automated cost calculations and instant replacement order creation.

= Key Features =

* **Quick Order Lookup** - Find orders by ID or customer phone number
* **Automatic Cost Calculation** - Instantly calculate price differences between returned and new products
* **Replacement Order Creation** - Create new orders with a single click
* **Coupon Generation** - Automatically create credit coupons for customers when they're owed money
* **Return History** - Track all processed returns in one place
* **Email Notifications** - Send customers their coupon codes automatically

= Free Version Includes =

* Process up to 20 returns per month
* Customer fault return reason
* Automatic cost calculations
* Replacement order creation
* Basic coupon generation
* Return history tracking

= Pro Version Features =

Upgrade to [Atlas Returns Pro](https://pluginatlas.com/atlas-returns-for-woocommerce) for:

* **Unlimited returns** - No monthly limit
* **All return reasons** - Customer fault, company fault with special handling, defective product replacement
* **Analytics Dashboard** - Visual charts and reports on return trends
* **CSV Export** - Export return data for reporting
* **Custom Email Templates** - Personalize customer communications
* **Priority Support** - Get help when you need it

= Perfect For =

* E-commerce stores with physical products
* Businesses handling product exchanges
* Stores offering product warranties
* Any WooCommerce shop processing returns

= How It Works =

1. Enter the order ID or customer phone number
2. Select the return reason
3. Enter the SKUs of products being returned
4. Enter the SKUs of replacement products
5. Review the automatic cost calculation
6. Click to create the replacement order

The plugin handles all the calculations, creates a properly priced replacement order, and can even generate credit coupons when customers are owed money.

= Requirements =

* WordPress 6.0 or higher
* WooCommerce 7.0 or higher
* PHP 7.4 or higher

= Support =

For support, feature requests, or bug reports, please visit our [support page](https://pluginatlas.com/contact).

== Installation ==

1. Upload the `atlas-returns-for-woocommerce` folder to the `/wp-content/plugins/` directory
2. Activate the plugin through the 'Plugins' menu in WordPress
3. Go to Atlas Returns in your admin menu to start processing returns
4. Configure settings under Atlas Returns > Settings

= Configuration =

After activation, navigate to **Atlas Returns > Settings** to configure:

* Shipping costs for customer-fault returns
* COD fees
* Coupon validity period
* Default payment method for replacement orders
* Email notification settings

== Frequently Asked Questions ==

= Does this plugin work with WooCommerce HPOS? =

Yes! Atlas Returns is fully compatible with WooCommerce High-Performance Order Storage (HPOS).

= Can I search for orders by phone number? =

Yes, you can enter either an order ID or the customer's billing phone number to find their order.

= What happens if the customer is owed money? =

If the new products cost less than the returned products, the plugin can automatically generate a coupon for the difference and email it to the customer.

= How do I upgrade to Pro? =

Visit [pluginatlas.com/atlas-returns](https://pluginatlas.com/atlas-returns-for-woocommerce) to purchase a Pro license. After purchase, you'll receive instructions to activate Pro features.

= Is there a limit on returns in the free version? =

Yes, the free version allows up to 20 returns per month. The Pro version has no limits.

= Can I track return history? =

Yes! Navigate to Atlas Returns > History to see all processed returns with details including original order, replacement order, reason, cost difference, and date.

== Screenshots ==

1. Main return processing screen
2. Order preview with items
3. Cost calculation breakdown
4. Settings page
5. Return history list
6. Analytics dashboard (Pro)

== Changelog ==

= 2.0.0 =
* Complete rewrite with modern architecture
* New PSR-4 compliant codebase
* Added return history tracking
* Added settings page with WordPress Settings API
* Added monthly return limit for free version
* Added Pro upgrade path
* Full WooCommerce HPOS compatibility
* Improved internationalization support
* Enhanced security with proper nonce verification
* Better error handling and user feedback

= 1.46 =
* Legacy version (original plugin)

== Upgrade Notice ==

= 2.0.0 =
Major update with new features, improved architecture, and Pro version support. Backup your site before upgrading.
