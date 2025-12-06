<?php
/**
 * Pro features loader.
 *
 * @package AtlasReturns\Pro
 */

namespace AtlasReturns\Pro;

use AtlasReturns\Pro\Analytics\Analytics;
use AtlasReturns\Pro\Analytics\DashboardWidget;
use AtlasReturns\Pro\Export\CsvExporter;

/**
 * Class ProLoader
 *
 * Loads all Pro features when Pro license is active.
 */
class ProLoader {

	/**
	 * Constructor.
	 */
	public function __construct() {
		$this->init();
	}

	/**
	 * Initialize Pro features.
	 */
	private function init() {
		// Analytics.
		new Analytics();

		// Dashboard widget.
		new DashboardWidget();

		// CSV Exporter.
		new CsvExporter();

		// Add Pro-specific hooks.
		$this->add_hooks();
	}

	/**
	 * Add Pro-specific hooks.
	 */
	private function add_hooks() {
		// Remove free version limitations.
		add_filter( 'atlr_monthly_limit_enabled', '__return_false' );

		// Add Pro badge to admin.
		add_action( 'atlr_after_plugin_title', array( $this, 'render_pro_badge' ) );

		// Enable all return reasons.
		add_filter( 'atlr_available_return_reasons', array( $this, 'enable_all_reasons' ) );
	}

	/**
	 * Render Pro badge.
	 */
	public function render_pro_badge() {
		echo '<span class="atlr-pro-active-badge">' . esc_html__( 'Pro', 'atlas-returns' ) . '</span>';
	}

	/**
	 * Enable all return reasons for Pro users.
	 *
	 * @param array $reasons Available reasons.
	 * @return array All reasons.
	 */
	public function enable_all_reasons( $reasons ) {
		return \AtlasReturns\Core\CostCalculator::get_return_reasons( true );
	}
}
