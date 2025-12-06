<?php
/**
 * Plugin activator.
 *
 * @package AtlasReturns
 */

namespace AtlasReturns;

/**
 * Class Activator
 *
 * Handles plugin activation tasks.
 */
class Activator {

	/**
	 * Database version for migrations.
	 */
	const DB_VERSION = '1.0.0';

	/**
	 * Activate the plugin.
	 */
	public static function activate() {
		// Check requirements before activation.
		if ( ! self::check_requirements() ) {
			deactivate_plugins( plugin_basename( ATLR_PLUGIN_FILE ) );
			wp_die(
				esc_html__( 'Atlas Returns for WooCommerce requires WooCommerce to be installed and active.', 'atlas-returns' ),
				esc_html__( 'Plugin Activation Error', 'atlas-returns' ),
				array( 'back_link' => true )
			);
		}

		// Create database tables.
		self::create_tables();

		// Add capabilities.
		self::add_capabilities();

		// Set default options.
		self::set_default_options();

		// Store activation time.
		update_option( 'atlr_activated_time', time() );

		// Store database version.
		update_option( 'atlr_db_version', self::DB_VERSION );

		// Flush rewrite rules.
		flush_rewrite_rules();
	}

	/**
	 * Check plugin requirements.
	 *
	 * @return bool True if requirements are met.
	 */
	private static function check_requirements() {
		// Check PHP version.
		if ( version_compare( PHP_VERSION, ATLR_MIN_PHP_VERSION, '<' ) ) {
			return false;
		}

		// WooCommerce check will happen on plugins_loaded.
		return true;
	}

	/**
	 * Create custom database tables.
	 */
	private static function create_tables() {
		global $wpdb;

		$charset_collate = $wpdb->get_charset_collate();
		$table_name      = $wpdb->prefix . 'atlr_returns';

		$sql = "CREATE TABLE IF NOT EXISTS {$table_name} (
            id bigint(20) unsigned NOT NULL AUTO_INCREMENT,
            original_order_id bigint(20) unsigned NOT NULL,
            return_order_id bigint(20) unsigned DEFAULT NULL,
            reason varchar(50) NOT NULL,
            status varchar(20) NOT NULL DEFAULT 'pending',
            return_products longtext NOT NULL,
            new_products longtext NOT NULL,
            cost_difference decimal(10,2) NOT NULL DEFAULT 0,
            shipping_cost decimal(10,2) NOT NULL DEFAULT 0,
            cod_fee decimal(10,2) NOT NULL DEFAULT 0,
            coupon_id bigint(20) unsigned DEFAULT NULL,
            notes text DEFAULT NULL,
            created_by bigint(20) unsigned NOT NULL,
            created_at datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
            updated_at datetime NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
            PRIMARY KEY (id),
            KEY original_order_id (original_order_id),
            KEY return_order_id (return_order_id),
            KEY reason (reason),
            KEY status (status),
            KEY created_at (created_at)
        ) {$charset_collate};";

		require_once ABSPATH . 'wp-admin/includes/upgrade.php';
		dbDelta( $sql );
	}

	/**
	 * Add plugin capabilities to roles.
	 */
	private static function add_capabilities() {
		// Add capability to administrator.
		$admin = get_role( 'administrator' );
		if ( $admin ) {
			$admin->add_cap( 'manage_atlas_returns' );
		}

		// Add capability to shop_manager.
		$shop_manager = get_role( 'shop_manager' );
		if ( $shop_manager ) {
			$shop_manager->add_cap( 'manage_atlas_returns' );
		}

		// Add capability to cooperator (custom role from previous plugin).
		$cooperator = get_role( 'cooperator' );
		if ( $cooperator ) {
			$cooperator->add_cap( 'manage_atlas_returns' );
		}
	}

	/**
	 * Set default plugin options.
	 */
	private static function set_default_options() {
		// Only set defaults if not already set.
		if ( false === get_option( 'atlr_shipping_cost' ) ) {
			update_option( 'atlr_shipping_cost', 2.00 );
		}

		if ( false === get_option( 'atlr_cod_fee' ) ) {
			update_option( 'atlr_cod_fee', 1.00 );
		}

		if ( false === get_option( 'atlr_coupon_validity_days' ) ) {
			update_option( 'atlr_coupon_validity_days', 180 );
		}

		if ( false === get_option( 'atlr_default_payment_method' ) ) {
			update_option( 'atlr_default_payment_method', 'cod' );
		}

		if ( false === get_option( 'atlr_special_handling_note' ) ) {
			update_option( 'atlr_special_handling_note', __( 'SPECIAL HANDLING - PICKUP ON DELIVERY', 'atlas-returns' ) );
		}

		if ( false === get_option( 'atlr_enable_email_notifications' ) ) {
			update_option( 'atlr_enable_email_notifications', 'yes' );
		}

		if ( false === get_option( 'atlr_monthly_return_limit' ) ) {
			update_option( 'atlr_monthly_return_limit', 20 ); // Free version limit.
		}
	}
}
