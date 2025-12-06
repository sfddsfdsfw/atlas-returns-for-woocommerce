<?php
/**
 * Main plugin class.
 *
 * @package AtlasReturns
 */

namespace AtlasReturns;

use AtlasReturns\Traits\Singleton;
use AtlasReturns\Admin\Admin;
use AtlasReturns\Admin\Settings;
use AtlasReturns\Api\AjaxHandler;

/**
 * Class Plugin
 *
 * Main plugin class that bootstraps all functionality.
 */
class Plugin {

	use Singleton;

	/**
	 * Constructor.
	 */
	private function __construct() {
		$this->init_hooks();
	}

	/**
	 * Initialize WordPress hooks.
	 */
	private function init_hooks() {
		// Admin functionality.
		if ( is_admin() ) {
			new Admin();
			new Settings();
		}

		// AJAX handlers (always load for admin-ajax.php).
		new AjaxHandler();

		// Load Pro features if available.
		if ( $this->is_pro() ) {
			$this->load_pro_features();
		}
	}

	/**
	 * Check if Pro version is active.
	 *
	 * @return bool True if Pro is active.
	 */
	public function is_pro() {
		// Development mode override - always check first.
		if ( defined( 'ATLR_DEV_PRO' ) && ATLR_DEV_PRO === true ) {
			return true;
		}

		// Check Freemius.
		if ( function_exists( 'atlr_fs' ) ) {
			return atlr_fs()->is_paying() || atlr_fs()->is_trial();
		}

		return false;
	}

	/**
	 * Check if user can create more returns this month (free plan limit).
	 *
	 * @return bool True if user can create more returns.
	 */
	public function can_create_return() {
		// Pro users have no limit.
		if ( $this->is_pro() ) {
			return true;
		}

		// Free plan: 20 returns per month.
		$monthly_limit = 20;
		$current_count = $this->get_monthly_return_count();

		return $current_count < $monthly_limit;
	}

	/**
	 * Get remaining returns for the month (free plan).
	 *
	 * @return int|string Number of remaining returns or 'unlimited' for Pro.
	 */
	public function get_remaining_returns() {
		if ( $this->is_pro() ) {
			return 'unlimited';
		}

		$monthly_limit = 20;
		$current_count = $this->get_monthly_return_count();

		return max( 0, $monthly_limit - $current_count );
	}

	/**
	 * Get the count of returns created this month.
	 *
	 * @return int Number of returns this month.
	 */
	public function get_monthly_return_count() {
		global $wpdb;

		$table_name = $wpdb->prefix . 'atlr_returns';
		$first_day  = gmdate( 'Y-m-01 00:00:00' );
		$last_day   = gmdate( 'Y-m-t 23:59:59' );

		// Check if table exists.
		// phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery,WordPress.DB.DirectDatabaseQuery.NoCaching -- Custom table check.
		if ( $wpdb->get_var( $wpdb->prepare( 'SHOW TABLES LIKE %s', $table_name ) ) !== $table_name ) {
			return 0;
		}

		// phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery,WordPress.DB.DirectDatabaseQuery.NoCaching,WordPress.DB.PreparedSQL.InterpolatedNotPrepared -- Custom table.
		return (int) $wpdb->get_var(
			$wpdb->prepare(
				"SELECT COUNT(*) FROM {$table_name} WHERE created_at BETWEEN %s AND %s",
				$first_day,
				$last_day
			)
		);
	}

	/**
	 * Get available return reasons based on plan.
	 *
	 * @return array Array of reason codes.
	 */
	public function get_available_reasons() {
		// Free plan only gets customer_fault.
		if ( ! $this->is_pro() ) {
			return array( 'customer_fault' );
		}

		// Pro gets all reasons.
		return array(
			'customer_fault',
			'company_fault',
			'company_fault_no_special_admin',
		);
	}

	/**
	 * Check if a specific reason is available for the current plan.
	 *
	 * @param string $reason Reason code.
	 * @return bool True if reason is available.
	 */
	public function is_reason_available( $reason ) {
		return in_array( $reason, $this->get_available_reasons(), true );
	}

	/**
	 * Load Pro features.
	 */
	private function load_pro_features() {
		if ( class_exists( '\AtlasReturns\Pro\ProLoader' ) ) {
			new \AtlasReturns\Pro\ProLoader();
		}
	}

	/**
	 * Get plugin version.
	 *
	 * @return string Plugin version.
	 */
	public function get_version() {
		return ATLR_VERSION;
	}

	/**
	 * Get plugin directory path.
	 *
	 * @return string Plugin directory path.
	 */
	public function get_plugin_dir() {
		return ATLR_PLUGIN_DIR;
	}

	/**
	 * Get plugin URL.
	 *
	 * @return string Plugin URL.
	 */
	public function get_plugin_url() {
		return ATLR_PLUGIN_URL;
	}
}
