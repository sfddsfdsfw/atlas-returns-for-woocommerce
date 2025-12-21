<?php
/**
 * Admin class.
 *
 * @package AtlasReturns\Admin
 */

namespace AtlasReturns\Admin;

/**
 * Class Admin
 *
 * Handles admin menu, pages, and assets.
 */
class Admin {

	/**
	 * Constructor.
	 */
	public function __construct() {
		add_action( 'admin_menu', array( $this, 'add_admin_menu' ) );
		add_action( 'admin_enqueue_scripts', array( $this, 'enqueue_assets' ) );
	}

	/**
	 * Add admin menu pages.
	 */
	public function add_admin_menu() {
		// Main menu page.
		add_menu_page(
			__( 'Atlas Returns', 'atlas-returns-for-woocommerce' ),
			__( 'Atlas Returns', 'atlas-returns-for-woocommerce' ),
			'manage_atlas_returns',
			'atlas-returns-for-woocommerce',
			array( $this, 'render_main_page' ),
			'dashicons-image-rotate',
			56
		);

		// Submenu - Returns (same as main).
		add_submenu_page(
			'atlas-returns-for-woocommerce',
			__( 'Process Return', 'atlas-returns-for-woocommerce' ),
			__( 'Process Return', 'atlas-returns-for-woocommerce' ),
			'manage_atlas_returns',
			'atlas-returns-for-woocommerce',
			array( $this, 'render_main_page' )
		);

		// Submenu - History.
		add_submenu_page(
			'atlas-returns-for-woocommerce',
			__( 'Return History', 'atlas-returns-for-woocommerce' ),
			__( 'History', 'atlas-returns-for-woocommerce' ),
			'manage_atlas_returns',
			'atlas-returns-history',
			array( $this, 'render_history_page' )
		);

		// Submenu - Settings.
		add_submenu_page(
			'atlas-returns-for-woocommerce',
			__( 'Settings', 'atlas-returns-for-woocommerce' ),
			__( 'Settings', 'atlas-returns-for-woocommerce' ),
			'manage_atlas_returns',
			'atlas-returns-settings',
			array( $this, 'render_settings_page' )
		);

		// Analytics submenu - shows Pro locked page for free users.
		$plugin          = \AtlasReturns\Plugin::instance();
		$analytics_title = $plugin->is_pro()
			? __( 'Analytics', 'atlas-returns-for-woocommerce' )
			: __( 'Analytics', 'atlas-returns-for-woocommerce' ) . ' <span class="atlr-pro-tag">Pro</span>';

		add_submenu_page(
			'atlas-returns-for-woocommerce',
			__( 'Analytics', 'atlas-returns-for-woocommerce' ),
			$analytics_title,
			'manage_atlas_returns',
			'atlas-returns-analytics',
			array( $this, 'render_analytics_page' )
		);

		// Pricing page for free users.
		if ( ! $plugin->is_pro() ) {
			add_submenu_page(
				'atlas-returns-for-woocommerce',
				__( 'Upgrade to Pro', 'atlas-returns-for-woocommerce' ),
				'<span style="color: #39b54a;">' . __( 'Upgrade to Pro', 'atlas-returns-for-woocommerce' ) . '</span>',
				'manage_atlas_returns',
				'atlas-returns-pricing',
				array( $this, 'render_pricing_page' )
			);
		}

		// Add inline CSS for Pro tag in menu.
		add_action( 'admin_head', array( $this, 'add_menu_styles' ) );
	}

	/**
	 * Add admin menu CSS.
	 */
	public function add_menu_styles() {
		wp_enqueue_style(
			'atlr-admin-components',
			ATLR_PLUGIN_URL . 'assets/dist/css/admin-components.min.css',
			array(),
			ATLR_VERSION
		);
	}

	/**
	 * Enqueue admin assets.
	 *
	 * @param string $hook_suffix The current admin page hook suffix.
	 */
	public function enqueue_assets( $hook_suffix ) {
		// Only load on our plugin pages.
		if ( strpos( $hook_suffix, 'atlas-returns-for-woocommerce' ) === false ) {
			return;
		}

		// Enqueue admin styles.
		wp_enqueue_style(
			'atlr-admin',
			ATLR_PLUGIN_URL . 'assets/dist/css/admin.min.css',
			array(),
			ATLR_VERSION
		);

		// Enqueue admin scripts.
		wp_enqueue_script(
			'atlr-admin',
			ATLR_PLUGIN_URL . 'assets/dist/js/admin.min.js',
			array( 'jquery' ),
			ATLR_VERSION,
			true
		);

		$plugin = \AtlasReturns\Plugin::instance();

		// Localize script.
		wp_localize_script(
			'atlr-admin',
			'atlrAdmin',
			array(
				'ajaxUrl'          => admin_url( 'admin-ajax.php' ),
				'nonce'            => wp_create_nonce( 'atlr_nonce' ),
				'isPro'            => $plugin->is_pro(),
				'canCreateReturn'  => $plugin->can_create_return(),
				'remainingReturns' => $plugin->get_remaining_returns(),
				'availableReasons' => $plugin->get_available_reasons(),
				'upgradeUrl'       => function_exists( 'atlr_fs' ) ? atlr_fs()->get_upgrade_url() : admin_url( 'admin.php?page=atlas-returns-pricing' ),
				'i18n'             => array(
					'loading'          => __( 'Loading...', 'atlas-returns-for-woocommerce' ),
					'error'            => __( 'An error occurred.', 'atlas-returns-for-woocommerce' ),
					'selectReason'     => __( 'Please select a return reason.', 'atlas-returns-for-woocommerce' ),
					'enterProducts'    => __( 'Please enter products to return.', 'atlas-returns-for-woocommerce' ),
					'enterNewProducts' => __( 'Please enter new products.', 'atlas-returns-for-woocommerce' ),
					'confirmSubmit'    => __( 'Are you sure you want to create this return order?', 'atlas-returns-for-woocommerce' ),
					'success'          => __( 'Return order created successfully!', 'atlas-returns-for-woocommerce' ),
					'limitReached'     => __( 'Monthly return limit reached. Upgrade to Pro for unlimited returns.', 'atlas-returns-for-woocommerce' ),
					'proFeature'       => __( 'This feature requires Atlas Returns Pro.', 'atlas-returns-for-woocommerce' ),
				),
			)
		);

		// Analytics page - load Chart.js and analytics scripts (Pro only).
		if ( strpos( $hook_suffix, 'analytics' ) !== false && $plugin->is_pro() ) {
			wp_enqueue_script(
				'chartjs',
				ATLR_PLUGIN_URL . 'assets/dist/js/chart.min.js',
				array(),
				'4.4.1',
				true
			);

			wp_enqueue_style(
				'atlr-analytics',
				ATLR_PLUGIN_URL . 'assets/dist/css/analytics.min.css',
				array(),
				ATLR_VERSION
			);

			wp_enqueue_script(
				'atlr-analytics',
				ATLR_PLUGIN_URL . 'assets/dist/js/analytics.min.js',
				array( 'jquery', 'chartjs' ),
				ATLR_VERSION,
				true
			);

			// Localize analytics script.
			wp_localize_script(
				'atlr-analytics',
				'atlrAnalytics',
				array(
					'ajaxUrl' => admin_url( 'admin-ajax.php' ),
					'nonce'   => wp_create_nonce( 'atlr_analytics_nonce' ),
					'colors'  => array(
						'primary'   => '#667eea',
						'secondary' => '#764ba2',
						'success'   => '#46b450',
						'warning'   => '#ffb900',
						'danger'    => '#dc3232',
						'info'      => '#00a0d2',
					),
					'i18n'    => array(
						'returns'        => __( 'Returns', 'atlas-returns-for-woocommerce' ),
						'refunded'       => __( 'Refunded', 'atlas-returns-for-woocommerce' ),
						'charged'        => __( 'Charged', 'atlas-returns-for-woocommerce' ),
						'costDifference' => __( 'Cost Difference', 'atlas-returns-for-woocommerce' ),
						'noData'         => __( 'No data available', 'atlas-returns-for-woocommerce' ),
					),
				)
			);
		}
	}

	/**
	 * Render the main admin page.
	 */
	public function render_main_page() {
		// Check user capability.
		if ( ! current_user_can( 'manage_atlas_returns' ) ) {
			wp_die( esc_html__( 'You do not have permission to access this page.', 'atlas-returns-for-woocommerce' ) );
		}

		$plugin = \AtlasReturns\Plugin::instance();

		// Check monthly limit for free version.
		if ( ! $plugin->is_pro() ) {
			$this->check_monthly_limit();
		}

		// Load view.
		include ATLR_PLUGIN_DIR . 'includes/Admin/views/admin-page.php';
	}

	/**
	 * Render the history page.
	 */
	public function render_history_page() {
		if ( ! current_user_can( 'manage_atlas_returns' ) ) {
			wp_die( esc_html__( 'You do not have permission to access this page.', 'atlas-returns-for-woocommerce' ) );
		}

		include ATLR_PLUGIN_DIR . 'includes/Admin/views/history-page.php';
	}

	/**
	 * Render the settings page.
	 */
	public function render_settings_page() {
		if ( ! current_user_can( 'manage_atlas_returns' ) ) {
			wp_die( esc_html__( 'You do not have permission to access this page.', 'atlas-returns-for-woocommerce' ) );
		}

		include ATLR_PLUGIN_DIR . 'includes/Admin/views/settings-page.php';
	}

	/**
	 * Render the analytics page (Pro only).
	 */
	public function render_analytics_page() {
		if ( ! current_user_can( 'manage_atlas_returns' ) ) {
			wp_die( esc_html__( 'You do not have permission to access this page.', 'atlas-returns-for-woocommerce' ) );
		}

		$plugin = \AtlasReturns\Plugin::instance();

		// Show Pro locked page for free users.
		if ( ! $plugin->is_pro() ) {
			$feature_name        = __( 'Analytics Dashboard', 'atlas-returns-for-woocommerce' );
			$feature_description = __( 'Get insights into your returns with charts, reports, and CSV export.', 'atlas-returns-for-woocommerce' );
			include ATLR_PLUGIN_DIR . 'includes/Admin/views/partials/pro-feature-locked.php';
			return;
		}

		include ATLR_PLUGIN_DIR . 'includes-pro/views/analytics-page.php';
	}

	/**
	 * Render the pricing page.
	 */
	public function render_pricing_page() {
		if ( ! current_user_can( 'manage_atlas_returns' ) ) {
			wp_die( esc_html__( 'You do not have permission to access this page.', 'atlas-returns-for-woocommerce' ) );
		}

		include ATLR_PLUGIN_DIR . 'includes/Admin/views/pricing-page.php';
	}

	/**
	 * Check monthly return limit for free version.
	 */
	private function check_monthly_limit() {
		$limit         = (int) get_option( 'atlr_monthly_return_limit', 20 );
		$count         = (int) get_option( 'atlr_monthly_returns_count', 0 );
		$reset_date    = get_option( 'atlr_monthly_returns_reset_date', '' );
		$current_month = gmdate( 'Y-m' );

		// Reset count if new month.
		if ( $reset_date !== $current_month ) {
			update_option( 'atlr_monthly_returns_count', 0 );
			update_option( 'atlr_monthly_returns_reset_date', $current_month );
			$count = 0;
		}

		// Store limit info for display.
		$this->monthly_limit_remaining = $limit - $count;
		$this->monthly_limit_reached   = $count >= $limit;
	}

	/**
	 * Monthly limit remaining (for free version).
	 *
	 * @var int
	 */
	public $monthly_limit_remaining = 0;

	/**
	 * Whether monthly limit is reached (for free version).
	 *
	 * @var bool
	 */
	public $monthly_limit_reached = false;
}
