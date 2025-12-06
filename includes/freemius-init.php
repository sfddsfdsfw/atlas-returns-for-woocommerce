<?php
/**
 * Freemius SDK initialization.
 *
 * @package AtlasReturns
 */

defined( 'ABSPATH' ) || exit;

/**
 * Initialize Freemius SDK.
 *
 * IMPORTANT: Replace the placeholder values below with your actual Freemius credentials.
 * You can get these from: https://dashboard.freemius.com/
 *
 * Steps to set up:
 * 1. Create account at freemius.com
 * 2. Add new plugin product
 * 3. Copy the plugin ID, public key, and replace below
 * 4. Download Freemius SDK and extract to /freemius/ folder
 *
 * @return Freemius
 */
function atlr_fs() {
	global $atlr_fs;

	if ( ! isset( $atlr_fs ) ) {
		// Check if Freemius SDK exists.
		$freemius_path = ATLR_PLUGIN_DIR . 'freemius/start.php';

		if ( ! file_exists( $freemius_path ) ) {
			// SDK not installed - return mock object for development.
			return atlr_fs_mock();
		}

		// Include Freemius SDK.
		require_once $freemius_path;

		$atlr_fs = fs_dynamic_init(
			array(
				'id'                  => '22170',
				'slug'                => 'atlas-returns-for-woocommerce',
				'type'                => 'plugin',
				'public_key'          => 'pk_4cb8f2227552c9730d7488db97809',
				'is_premium'          => false,
				'premium_suffix'      => 'Pro',
				'has_premium_version' => true,
				'has_addons'          => false,
				'has_paid_plans'      => true,
				'menu'                => array(
					'slug'    => 'atlas-returns',
					'contact' => true,
					'support' => false,
					'parent'  => array(
						'slug' => 'woocommerce',
					),
				),
				'is_live'             => true,
			)
		);
	}

	return $atlr_fs;
}

/**
 * Create a mock Freemius object for development when SDK is not installed.
 *
 * This allows the plugin to work without the Freemius SDK during development.
 *
 * @return object Mock Freemius object.
 */
function atlr_fs_mock() {
	static $mock;

	if ( ! isset( $mock ) ) {
		$mock = new class() {
			/**
			 * Check if user is paying.
			 *
			 * @return bool Always false in mock mode.
			 */
			public function is_paying() {
				// For development, check for a constant to simulate Pro.
				return defined( 'ATLR_DEV_PRO' ) && ATLR_DEV_PRO;
			}

			/**
			 * Check if user is on free plan.
			 *
			 * @return bool Always true in mock mode (unless dev Pro enabled).
			 */
			public function is_free_plan() {
				return ! $this->is_paying();
			}

			/**
			 * Check if user can use premium code.
			 *
			 * @return bool Same as is_paying in mock.
			 */
			public function can_use_premium_code() {
				return $this->is_paying();
			}

			/**
			 * Check if user can use premium code (alias).
			 *
			 * @return bool Same as is_paying in mock.
			 */
			public function is_premium() {
				return $this->is_paying();
			}

			/**
			 * Check if trial is available.
			 *
			 * @return bool Always true in mock mode.
			 */
			public function is_trial_available() {
				return true;
			}

			/**
			 * Check if user is in trial.
			 *
			 * @return bool Always false in mock mode.
			 */
			public function is_trial() {
				return false;
			}

			/**
			 * Get account URL.
			 *
			 * @return string Empty in mock mode.
			 */
			public function get_account_url() {
				return admin_url( 'admin.php?page=atlas-returns-pricing' );
			}

			/**
			 * Get upgrade URL.
			 *
			 * @return string Pricing page URL.
			 */
			public function get_upgrade_url() {
				return admin_url( 'admin.php?page=atlas-returns-pricing' );
			}

			/**
			 * Get pricing URL.
			 *
			 * @param string $period Billing period.
			 * @param bool   $is_trial Whether to show trial.
			 * @return string Pricing page URL.
			 */
			public function pricing_url( $period = 'annual', $is_trial = false ) {
				return admin_url( 'admin.php?page=atlas-returns-pricing' );
			}

			/**
			 * Check if premium only.
			 *
			 * @return bool Always false for this plugin.
			 */
			public function is_premium_only() {
				return false;
			}

			/**
			 * Add filter.
			 *
			 * @param string   $tag      Filter tag.
			 * @param callable $callback Callback function.
			 * @param int      $priority Priority.
			 * @param int      $args     Number of arguments.
			 */
			public function add_filter( $tag, $callback, $priority = 10, $args = 1 ) {
				add_filter( $tag, $callback, $priority, $args );
			}

			/**
			 * Add action.
			 *
			 * @param string   $tag      Action tag.
			 * @param callable $callback Callback function.
			 * @param int      $priority Priority.
			 * @param int      $args     Number of arguments.
			 */
			public function add_action( $tag, $callback, $priority = 10, $args = 1 ) {
				add_action( $tag, $callback, $priority, $args );
			}

			/**
			 * Check if SDK is connected.
			 *
			 * @return bool Always false in mock mode.
			 */
			public function is_registered() {
				return false;
			}

			/**
			 * Check anonymous mode.
			 *
			 * @return bool Always true in mock mode.
			 */
			public function is_anonymous() {
				return true;
			}
		};
	}

	return $mock;
}

// Initialize Freemius.
atlr_fs();

// Signal that SDK was initiated.
do_action( 'atlr_fs_loaded' );
