<?php
/**
 * Plugin Name: Atlas Returns for WooCommerce
 * Plugin URI: https://pluginatlas.com/atlas-returns
 * Description: Professional return and exchange management for WooCommerce stores. Streamline returns, calculate costs, and create replacement orders with ease.
 * Version: 2.0.0
 * Author: PluginAtlas
 * Author URI: https://pluginatlas.com
 * License: GPL v2 or later
 * License URI: https://www.gnu.org/licenses/gpl-2.0.html
 * Text Domain: atlas-returns
 * Domain Path: /languages
 * Requires at least: 6.0
 * Requires PHP: 7.4
 * WC requires at least: 7.0
 * WC tested up to: 9.4
 *
 * @package AtlasReturns
 *
 * @fs_premium_only /includes-pro/
 */

// Exit if accessed directly.
defined( 'ABSPATH' ) || exit;

/**
 * Plugin version.
 */
define( 'ATLR_VERSION', '2.0.0' );

/**
 * Plugin file path.
 */
define( 'ATLR_PLUGIN_FILE', __FILE__ );

/**
 * Plugin directory path.
 */
define( 'ATLR_PLUGIN_DIR', plugin_dir_path( __FILE__ ) );

/**
 * Plugin directory URL.
 */
define( 'ATLR_PLUGIN_URL', plugin_dir_url( __FILE__ ) );

/**
 * Plugin basename.
 */
define( 'ATLR_PLUGIN_BASENAME', plugin_basename( __FILE__ ) );

/**
 * Minimum PHP version.
 */
define( 'ATLR_MIN_PHP_VERSION', '7.4' );

/**
 * Minimum WordPress version.
 */
define( 'ATLR_MIN_WP_VERSION', '6.0' );

/**
 * Minimum WooCommerce version.
 */
define( 'ATLR_MIN_WC_VERSION', '7.0' );

/**
 * Development mode: Set to true to simulate Pro features during development.
 */
if ( ! defined( 'ATLR_DEV_PRO' ) ) {
	define( 'ATLR_DEV_PRO', false );
}

/**
 * Load Composer autoloader if available.
 */
if ( file_exists( ATLR_PLUGIN_DIR . 'vendor/autoload.php' ) ) {
	require_once ATLR_PLUGIN_DIR . 'vendor/autoload.php';
} else {
	// Manual autoloader for when Composer is not used.
	spl_autoload_register(
		function ( $class ) {
			// Project-specific namespace prefix.
			$prefix = 'AtlasReturns\\';

			// Base directory for the namespace prefix.
			$base_dir = ATLR_PLUGIN_DIR . 'includes/';

			// Check if class uses our namespace prefix.
			$len = strlen( $prefix );
			if ( strncmp( $prefix, $class, $len ) !== 0 ) {
				return;
			}

			// Get the relative class name.
			$relative_class = substr( $class, $len );

			// Check for Pro namespace.
			if ( strpos( $relative_class, 'Pro\\' ) === 0 ) {
				$relative_class = substr( $relative_class, 4 );
				$base_dir       = ATLR_PLUGIN_DIR . 'includes-pro/';
			}

			// Replace namespace separators with directory separators.
			$file = $base_dir . str_replace( '\\', '/', $relative_class ) . '.php';

			// If the file exists, require it.
			if ( file_exists( $file ) ) {
				require $file;
			}
		}
	);
}

/**
 * Initialize Freemius SDK.
 * Must be called before any other plugin code.
 */
if ( ! function_exists( 'atlr_fs' ) ) {
	require_once ATLR_PLUGIN_DIR . 'includes/freemius-init.php';
}

/**
 * Check plugin requirements.
 *
 * @return bool True if requirements are met, false otherwise.
 */
function atlr_check_requirements() {
	$errors = array();

	// Check PHP version.
	if ( version_compare( PHP_VERSION, ATLR_MIN_PHP_VERSION, '<' ) ) {
		$errors[] = sprintf(
			/* translators: 1: Current PHP version, 2: Required PHP version */
			__( 'Atlas Returns requires PHP version %2$s or higher. You are running version %1$s.', 'atlas-returns' ),
			PHP_VERSION,
			ATLR_MIN_PHP_VERSION
		);
	}

	// Check WordPress version.
	if ( version_compare( get_bloginfo( 'version' ), ATLR_MIN_WP_VERSION, '<' ) ) {
		$errors[] = sprintf(
			/* translators: 1: Current WordPress version, 2: Required WordPress version */
			__( 'Atlas Returns requires WordPress version %2$s or higher. You are running version %1$s.', 'atlas-returns' ),
			get_bloginfo( 'version' ),
			ATLR_MIN_WP_VERSION
		);
	}

	// Check WooCommerce.
	if ( ! class_exists( 'WooCommerce' ) ) {
		$errors[] = __( 'Atlas Returns requires WooCommerce to be installed and active.', 'atlas-returns' );
	} elseif ( defined( 'WC_VERSION' ) && version_compare( WC_VERSION, ATLR_MIN_WC_VERSION, '<' ) ) {
		$errors[] = sprintf(
			/* translators: 1: Current WooCommerce version, 2: Required WooCommerce version */
			__( 'Atlas Returns requires WooCommerce version %2$s or higher. You are running version %1$s.', 'atlas-returns' ),
			WC_VERSION,
			ATLR_MIN_WC_VERSION
		);
	}

	if ( ! empty( $errors ) ) {
		// Store errors for admin notice.
		update_option( 'atlr_activation_errors', $errors );
		return false;
	}

	delete_option( 'atlr_activation_errors' );
	return true;
}

/**
 * Display admin notice for activation errors.
 */
function atlr_admin_notice_requirements() {
	$errors = get_option( 'atlr_activation_errors', array() );

	if ( empty( $errors ) ) {
		return;
	}

	echo '<div class="notice notice-error">';
	echo '<p><strong>' . esc_html__( 'Atlas Returns for WooCommerce', 'atlas-returns' ) . '</strong></p>';
	echo '<ul>';
	foreach ( $errors as $error ) {
		echo '<li>' . esc_html( $error ) . '</li>';
	}
	echo '</ul>';
	echo '</div>';
}
add_action( 'admin_notices', 'atlr_admin_notice_requirements' );

/**
 * Initialize the plugin.
 */
function atlr_init() {
	// Load text domain.
	load_plugin_textdomain( 'atlas-returns', false, dirname( ATLR_PLUGIN_BASENAME ) . '/languages' );

	// Check requirements.
	if ( ! atlr_check_requirements() ) {
		return;
	}

	// Initialize main plugin class.
	\AtlasReturns\Plugin::instance();
}
add_action( 'plugins_loaded', 'atlr_init' );

/**
 * Activation hook.
 */
register_activation_hook( __FILE__, array( \AtlasReturns\Activator::class, 'activate' ) );

/**
 * Deactivation hook.
 */
register_deactivation_hook( __FILE__, array( \AtlasReturns\Deactivator::class, 'deactivate' ) );

/**
 * Declare HPOS (High-Performance Order Storage) compatibility.
 */
add_action(
	'before_woocommerce_init',
	function () {
		if ( class_exists( \Automattic\WooCommerce\Utilities\FeaturesUtil::class ) ) {
			\Automattic\WooCommerce\Utilities\FeaturesUtil::declare_compatibility( 'custom_order_tables', __FILE__, true );
		}
	}
);

/**
 * Add settings link on plugins page.
 *
 * @param array $links Existing plugin action links.
 * @return array Modified plugin action links.
 */
function atlr_plugin_action_links( $links ) {
	$settings_link = sprintf(
		'<a href="%s">%s</a>',
		admin_url( 'admin.php?page=atlas-returns-settings' ),
		__( 'Settings', 'atlas-returns' )
	);

	array_unshift( $links, $settings_link );

	// Add upgrade link for free users.
	if ( function_exists( 'atlr_fs' ) && ! atlr_fs()->is_paying() ) {
		$links['upgrade'] = sprintf(
			'<a href="%s" style="color: #39b54a; font-weight: bold;">%s</a>',
			atlr_fs()->get_upgrade_url(),
			__( 'Upgrade to Pro', 'atlas-returns' )
		);
	}

	return $links;
}
add_filter( 'plugin_action_links_' . ATLR_PLUGIN_BASENAME, 'atlr_plugin_action_links' );

/**
 * Freemius uninstall cleanup hook.
 */
if ( function_exists( 'atlr_fs' ) ) {
	atlr_fs()->add_action( 'after_uninstall', 'atlr_fs_uninstall_cleanup' );
}

/**
 * Cleanup on Freemius uninstall.
 */
function atlr_fs_uninstall_cleanup() {
	// This is handled by uninstall.php, but Freemius may call this directly.
	// The uninstall.php file handles all cleanup.
}
