<?php
/**
 * Analytics controller.
 *
 * @package AtlasReturns\Pro\Analytics
 */

namespace AtlasReturns\Pro\Analytics;

/**
 * Class Analytics
 *
 * Handles the analytics page and AJAX endpoints.
 */
class Analytics {

	/**
	 * Reports instance.
	 *
	 * @var Reports
	 */
	private $reports;

	/**
	 * Constructor.
	 */
	public function __construct() {
		$this->reports = new Reports();
		$this->init_hooks();
	}

	/**
	 * Initialize hooks.
	 */
	private function init_hooks() {
		// AJAX endpoints for analytics data.
		add_action( 'wp_ajax_atlr_get_analytics_data', array( $this, 'get_analytics_data' ) );
		add_action( 'wp_ajax_atlr_get_chart_data', array( $this, 'get_chart_data' ) );

		// Enqueue analytics assets.
		add_action( 'admin_enqueue_scripts', array( $this, 'enqueue_assets' ) );
	}

	/**
	 * Enqueue analytics assets.
	 *
	 * @param string $hook_suffix Current admin page.
	 */
	public function enqueue_assets( $hook_suffix ) {
		if ( strpos( $hook_suffix, 'atlas-returns-analytics' ) === false ) {
			return;
		}

		// Chart.js from CDN (or local).
		wp_enqueue_script(
			'chartjs',
			'https://cdn.jsdelivr.net/npm/chart.js@4.4.0/dist/chart.umd.min.js',
			array(),
			'4.4.0',
			true
		);

		// Analytics script.
		wp_enqueue_script(
			'atlr-analytics',
			ATLR_PLUGIN_URL . 'assets/dist/js/analytics.min.js',
			array( 'jquery', 'chartjs' ),
			ATLR_VERSION,
			true
		);

		// Analytics styles.
		wp_enqueue_style(
			'atlr-analytics',
			ATLR_PLUGIN_URL . 'assets/dist/css/analytics.min.css',
			array( 'atlr-admin' ),
			ATLR_VERSION
		);

		// Localize script.
		wp_localize_script(
			'atlr-analytics',
			'atlrAnalytics',
			array(
				'ajaxUrl' => admin_url( 'admin-ajax.php' ),
				'nonce'   => wp_create_nonce( 'atlr_analytics_nonce' ),
				'i18n'    => array(
					'returns'        => __( 'Returns', 'atlas-returns' ),
					'costDifference' => __( 'Cost Difference', 'atlas-returns' ),
					'customerFault'  => __( 'Customer Fault', 'atlas-returns' ),
					'companyFault'   => __( 'Company Fault', 'atlas-returns' ),
					'defective'      => __( 'Defective', 'atlas-returns' ),
					'noData'         => __( 'No data available', 'atlas-returns' ),
					'loading'        => __( 'Loading...', 'atlas-returns' ),
					'refunded'       => __( 'Refunded', 'atlas-returns' ),
					'charged'        => __( 'Charged', 'atlas-returns' ),
				),
				'colors'  => array(
					'primary'   => '#667eea',
					'secondary' => '#764ba2',
					'success'   => '#46b450',
					'warning'   => '#ffb900',
					'danger'    => '#dc3232',
					'info'      => '#00a0d2',
				),
			)
		);
	}

	/**
	 * Get analytics data via AJAX.
	 */
	public function get_analytics_data() {
		check_ajax_referer( 'atlr_analytics_nonce', 'nonce' );

		if ( ! current_user_can( 'manage_atlas_returns' ) ) {
			wp_send_json_error( __( 'Permission denied.', 'atlas-returns' ) );
		}

		$period = isset( $_POST['period'] ) ? sanitize_text_field( wp_unslash( $_POST['period'] ) ) : '30days';

		$dates = $this->get_date_range( $period );

		$data = array(
			'summary'      => $this->reports->get_summary( $dates['from'], $dates['to'] ),
			'by_reason'    => $this->reports->get_by_reason( $dates['from'], $dates['to'] ),
			'trend'        => $this->reports->get_trend( $dates['from'], $dates['to'] ),
			'top_products' => $this->reports->get_top_returned_products( 10, $dates['from'], $dates['to'] ),
			'recent'       => $this->reports->get_recent_returns( 10 ),
		);

		wp_send_json_success( $data );
	}

	/**
	 * Get chart data via AJAX.
	 */
	public function get_chart_data() {
		check_ajax_referer( 'atlr_analytics_nonce', 'nonce' );

		if ( ! current_user_can( 'manage_atlas_returns' ) ) {
			wp_send_json_error( __( 'Permission denied.', 'atlas-returns' ) );
		}

		$chart  = isset( $_POST['chart'] ) ? sanitize_text_field( wp_unslash( $_POST['chart'] ) ) : '';
		$period = isset( $_POST['period'] ) ? sanitize_text_field( wp_unslash( $_POST['period'] ) ) : '30days';

		$dates = $this->get_date_range( $period );

		$data = array();

		switch ( $chart ) {
			case 'reasons':
				$data = $this->reports->get_by_reason( $dates['from'], $dates['to'] );
				break;

			case 'trend':
				$data = $this->reports->get_trend( $dates['from'], $dates['to'] );
				break;

			case 'costs':
				$data = $this->reports->get_cost_breakdown( $dates['from'], $dates['to'] );
				break;

			default:
				wp_send_json_error( __( 'Invalid chart type.', 'atlas-returns' ) );
		}

		wp_send_json_success( $data );
	}

	/**
	 * Get date range from period string.
	 *
	 * @param string $period Period string.
	 * @return array Date range with 'from' and 'to' keys.
	 */
	private function get_date_range( $period ) {
		$to   = gmdate( 'Y-m-d' );
		$from = $to;

		switch ( $period ) {
			case '7days':
				$from = gmdate( 'Y-m-d', strtotime( '-7 days' ) );
				break;

			case '30days':
				$from = gmdate( 'Y-m-d', strtotime( '-30 days' ) );
				break;

			case '90days':
				$from = gmdate( 'Y-m-d', strtotime( '-90 days' ) );
				break;

			case '365days':
			case 'year':
				$from = gmdate( 'Y-m-d', strtotime( '-365 days' ) );
				break;

			case 'month':
				$from = gmdate( 'Y-m-01' );
				break;

			case 'last_month':
				$from = gmdate( 'Y-m-01', strtotime( 'first day of last month' ) );
				$to   = gmdate( 'Y-m-t', strtotime( 'last day of last month' ) );
				break;

			case 'all':
				$from = '2000-01-01';
				break;

			default:
				$from = gmdate( 'Y-m-d', strtotime( '-30 days' ) );
		}

		return array(
			'from' => $from,
			'to'   => $to,
		);
	}
}
