<?php
/**
 * CSV exporter class.
 *
 * @package AtlasReturns\Pro\Export
 */

namespace AtlasReturns\Pro\Export;

use AtlasReturns\Core\CostCalculator;

/**
 * Class CsvExporter
 *
 * Handles CSV export of return data.
 */
class CsvExporter {

	/**
	 * Constructor.
	 */
	public function __construct() {
		add_action( 'wp_ajax_atlr_export_csv', array( $this, 'export_csv' ) );
		add_action( 'admin_init', array( $this, 'handle_export_request' ) );
	}

	/**
	 * Handle export request via GET parameter.
	 */
	public function handle_export_request() {
		// phpcs:ignore WordPress.Security.NonceVerification.Recommended -- Early return check, nonce verified below.
		if ( ! isset( $_GET['atlr_export'] ) || 'csv' !== $_GET['atlr_export'] ) {
			return;
		}

		if ( ! isset( $_GET['_wpnonce'] ) || ! wp_verify_nonce( sanitize_text_field( wp_unslash( $_GET['_wpnonce'] ) ), 'atlr_export_csv' ) ) {
			wp_die( esc_html__( 'Security check failed.', 'atlas-returns-for-woocommerce' ) );
		}

		if ( ! current_user_can( 'manage_atlas_returns' ) ) {
			wp_die( esc_html__( 'Permission denied.', 'atlas-returns-for-woocommerce' ) );
		}

		$this->generate_csv();
	}

	/**
	 * Export CSV via AJAX.
	 */
	public function export_csv() {
		check_ajax_referer( 'atlr_analytics_nonce', 'nonce' );

		if ( ! current_user_can( 'manage_atlas_returns' ) ) {
			wp_send_json_error( __( 'Permission denied.', 'atlas-returns-for-woocommerce' ) );
		}

		// Generate export URL with nonce.
		// phpcs:disable WordPress.Security.NonceVerification.Missing -- Nonce verified by check_ajax_referer above.
		$export_url = add_query_arg(
			array(
				'atlr_export' => 'csv',
				'_wpnonce'    => wp_create_nonce( 'atlr_export_csv' ),
				'date_from'   => isset( $_POST['date_from'] ) ? sanitize_text_field( wp_unslash( $_POST['date_from'] ) ) : '',
				'date_to'     => isset( $_POST['date_to'] ) ? sanitize_text_field( wp_unslash( $_POST['date_to'] ) ) : '',
			),
			admin_url( 'admin.php' )
		);
		// phpcs:enable WordPress.Security.NonceVerification.Missing

		wp_send_json_success( array( 'url' => $export_url ) );
	}

	/**
	 * Generate and download CSV file.
	 */
	private function generate_csv() {
		global $wpdb;

		$table_name = $wpdb->prefix . 'atlr_returns';

		// Get date filters.
		// phpcs:disable WordPress.Security.NonceVerification.Recommended -- Nonce verified in handle_export_request().
		$date_from = isset( $_GET['date_from'] ) ? sanitize_text_field( wp_unslash( $_GET['date_from'] ) ) : '';
		$date_to   = isset( $_GET['date_to'] ) ? sanitize_text_field( wp_unslash( $_GET['date_to'] ) ) : '';
		// phpcs:enable WordPress.Security.NonceVerification.Recommended

		// Build query.
		$where = '1=1';
		if ( $date_from ) {
			$where .= $wpdb->prepare( ' AND created_at >= %s', $date_from . ' 00:00:00' );
		}
		if ( $date_to ) {
			$where .= $wpdb->prepare( ' AND created_at <= %s', $date_to . ' 23:59:59' );
		}

		$returns = $wpdb->get_results(
			"SELECT * FROM {$table_name} WHERE {$where} ORDER BY created_at DESC", // phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared
			ARRAY_A
		);

		// Get reason labels.
		$reason_labels = CostCalculator::get_return_reasons( true );

		// Set headers for download.
		$filename = 'atlas-returns-export-' . gmdate( 'Y-m-d-His' ) . '.csv';

		header( 'Content-Type: text/csv; charset=utf-8' );
		header( 'Content-Disposition: attachment; filename="' . $filename . '"' );
		header( 'Pragma: no-cache' );
		header( 'Expires: 0' );

		// phpcs:disable WordPress.WP.AlternativeFunctions.file_system_operations_fopen,WordPress.WP.AlternativeFunctions.file_system_operations_fclose -- Writing to php://output for CSV download, WP_Filesystem not applicable.
		$output = fopen( 'php://output', 'w' );

		// Add BOM for Excel UTF-8 compatibility.
		fprintf( $output, chr( 0xEF ) . chr( 0xBB ) . chr( 0xBF ) );

		// CSV headers.
		fputcsv(
			$output,
			array(
				__( 'ID', 'atlas-returns-for-woocommerce' ),
				__( 'Original Order ID', 'atlas-returns-for-woocommerce' ),
				__( 'Return Order ID', 'atlas-returns-for-woocommerce' ),
				__( 'Reason', 'atlas-returns-for-woocommerce' ),
				__( 'Status', 'atlas-returns-for-woocommerce' ),
				__( 'Return Products (SKUs)', 'atlas-returns-for-woocommerce' ),
				__( 'New Products (SKUs)', 'atlas-returns-for-woocommerce' ),
				__( 'Cost Difference', 'atlas-returns-for-woocommerce' ),
				__( 'Shipping Cost', 'atlas-returns-for-woocommerce' ),
				__( 'COD Fee', 'atlas-returns-for-woocommerce' ),
				__( 'Coupon ID', 'atlas-returns-for-woocommerce' ),
				__( 'Created By', 'atlas-returns-for-woocommerce' ),
				__( 'Created At', 'atlas-returns-for-woocommerce' ),
			)
		);

		// CSV data rows.
		foreach ( $returns as $return ) {
			$user         = get_userdata( $return['created_by'] );
			$created_by   = $user ? $user->display_name : __( 'Unknown', 'atlas-returns-for-woocommerce' );
			$reason_label = $reason_labels[ $return['reason'] ] ?? $return['reason'];

			// Parse JSON SKUs.
			$return_skus = json_decode( $return['return_products'], true );
			$new_skus    = json_decode( $return['new_products'], true );

			fputcsv(
				$output,
				array(
					$return['id'],
					$return['original_order_id'],
					$return['return_order_id'] ?: '',
					$reason_label,
					ucfirst( $return['status'] ),
					is_array( $return_skus ) ? implode( ', ', $return_skus ) : '',
					is_array( $new_skus ) ? implode( ', ', $new_skus ) : '',
					number_format( (float) $return['cost_difference'], 2 ),
					number_format( (float) $return['shipping_cost'], 2 ),
					number_format( (float) $return['cod_fee'], 2 ),
					$return['coupon_id'] ?: '',
					$created_by,
					$return['created_at'],
				)
			);
		}

		fclose( $output );
		// phpcs:enable WordPress.WP.AlternativeFunctions.file_system_operations_fopen,WordPress.WP.AlternativeFunctions.file_system_operations_fclose
		exit;
	}
}
