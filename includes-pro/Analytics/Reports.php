<?php
/**
 * Reports class for analytics data.
 *
 * @package AtlasReturns\Pro\Analytics
 */

namespace AtlasReturns\Pro\Analytics;

use AtlasReturns\Core\CostCalculator;

/**
 * Class Reports
 *
 * Generates reports and statistics for the analytics dashboard.
 */
class Reports {

	/**
	 * Table name.
	 *
	 * @var string
	 */
	private $table_name;

	/**
	 * Constructor.
	 */
	public function __construct() {
		global $wpdb;
		$this->table_name = $wpdb->prefix . 'atlr_returns';
	}

	/**
	 * Get summary statistics.
	 *
	 * @param string $date_from Start date (Y-m-d).
	 * @param string $date_to End date (Y-m-d).
	 * @return array Summary data.
	 */
	public function get_summary( $date_from, $date_to ) {
		global $wpdb;

		$where = $this->build_date_where( $date_from, $date_to );

		$result = $wpdb->get_row(
			"SELECT
                COUNT(*) as total_returns,
                COALESCE(SUM(cost_difference), 0) as total_cost_difference,
                COALESCE(AVG(cost_difference), 0) as avg_cost_difference,
                COALESCE(SUM(CASE WHEN cost_difference < 0 THEN ABS(cost_difference) ELSE 0 END), 0) as total_refunded,
                COALESCE(SUM(CASE WHEN cost_difference > 0 THEN cost_difference ELSE 0 END), 0) as total_charged,
                COUNT(DISTINCT original_order_id) as unique_orders
             FROM {$this->table_name}
             WHERE {$where}", // phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared
			ARRAY_A
		);

		// Calculate return rate (returns / total orders in period).
		$total_orders = $this->get_total_orders_count( $date_from, $date_to );
		$return_rate  = $total_orders > 0 ? ( $result['total_returns'] / $total_orders ) * 100 : 0;

		$result['total_orders'] = $total_orders;
		$result['return_rate']  = round( $return_rate, 2 );

		// Format numbers.
		$result['total_cost_difference'] = (float) $result['total_cost_difference'];
		$result['avg_cost_difference']   = round( (float) $result['avg_cost_difference'], 2 );
		$result['total_refunded']        = (float) $result['total_refunded'];
		$result['total_charged']         = (float) $result['total_charged'];

		return $result;
	}

	/**
	 * Get returns grouped by reason.
	 *
	 * @param string $date_from Start date (Y-m-d).
	 * @param string $date_to End date (Y-m-d).
	 * @return array Returns by reason.
	 */
	public function get_by_reason( $date_from, $date_to ) {
		global $wpdb;

		$where = $this->build_date_where( $date_from, $date_to );

		$results = $wpdb->get_results(
			"SELECT
                reason,
                COUNT(*) as count,
                COALESCE(SUM(cost_difference), 0) as total_cost,
                COALESCE(AVG(cost_difference), 0) as avg_cost
             FROM {$this->table_name}
             WHERE {$where}
             GROUP BY reason
             ORDER BY count DESC", // phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared
			ARRAY_A
		);

		// Add reason labels.
		$reason_labels = CostCalculator::get_return_reasons( true );
		foreach ( $results as &$row ) {
			$row['label']      = $reason_labels[ $row['reason'] ] ?? $row['reason'];
			$row['count']      = (int) $row['count'];
			$row['total_cost'] = (float) $row['total_cost'];
			$row['avg_cost']   = round( (float) $row['avg_cost'], 2 );
		}

		return $results;
	}

	/**
	 * Get returns trend over time.
	 *
	 * @param string $date_from Start date (Y-m-d).
	 * @param string $date_to End date (Y-m-d).
	 * @return array Trend data.
	 */
	public function get_trend( $date_from, $date_to ) {
		global $wpdb;

		// Determine grouping based on date range.
		$days_diff = ( strtotime( $date_to ) - strtotime( $date_from ) ) / DAY_IN_SECONDS;

		if ( $days_diff <= 31 ) {
			$date_format = '%Y-%m-%d';
			$group_by    = 'day';
		} elseif ( $days_diff <= 90 ) {
			$date_format = '%Y-%u'; // Week number.
			$group_by    = 'week';
		} else {
			$date_format = '%Y-%m';
			$group_by    = 'month';
		}

		$results = $wpdb->get_results(
			$wpdb->prepare(
				"SELECT
                    DATE_FORMAT(created_at, %s) as period,
                    COUNT(*) as count,
                    COALESCE(SUM(cost_difference), 0) as total_cost,
                    COALESCE(SUM(CASE WHEN cost_difference < 0 THEN ABS(cost_difference) ELSE 0 END), 0) as refunded,
                    COALESCE(SUM(CASE WHEN cost_difference > 0 THEN cost_difference ELSE 0 END), 0) as charged
                 FROM {$this->table_name}
                 WHERE created_at >= %s AND created_at <= %s
                 GROUP BY period
                 ORDER BY period ASC",
				$date_format,
				$date_from . ' 00:00:00',
				$date_to . ' 23:59:59'
			),
			ARRAY_A
		);

		// Fill in missing dates for consistent charting.
		$filled_results = $this->fill_date_gaps( $results, $date_from, $date_to, $group_by );

		return array(
			'data'     => $filled_results,
			'group_by' => $group_by,
		);
	}

	/**
	 * Get cost breakdown.
	 *
	 * @param string $date_from Start date (Y-m-d).
	 * @param string $date_to End date (Y-m-d).
	 * @return array Cost breakdown.
	 */
	public function get_cost_breakdown( $date_from, $date_to ) {
		global $wpdb;

		$where = $this->build_date_where( $date_from, $date_to );

		$result = $wpdb->get_row(
			"SELECT
                COALESCE(SUM(CASE WHEN cost_difference < 0 THEN ABS(cost_difference) ELSE 0 END), 0) as refunded,
                COALESCE(SUM(CASE WHEN cost_difference > 0 THEN cost_difference ELSE 0 END), 0) as charged,
                COALESCE(SUM(shipping_cost), 0) as shipping,
                COALESCE(SUM(cod_fee), 0) as cod_fees
             FROM {$this->table_name}
             WHERE {$where}", // phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared
			ARRAY_A
		);

		return array(
			'refunded' => (float) $result['refunded'],
			'charged'  => (float) $result['charged'],
			'shipping' => (float) $result['shipping'],
			'cod_fees' => (float) $result['cod_fees'],
		);
	}

	/**
	 * Get top returned products.
	 *
	 * @param int    $limit Number of products to return.
	 * @param string $date_from Start date (Y-m-d).
	 * @param string $date_to End date (Y-m-d).
	 * @return array Top returned products.
	 */
	public function get_top_returned_products( $limit = 10, $date_from = null, $date_to = null ) {
		global $wpdb;

		$where = '1=1';
		if ( $date_from ) {
			$where .= $wpdb->prepare( ' AND created_at >= %s', $date_from . ' 00:00:00' );
		}
		if ( $date_to ) {
			$where .= $wpdb->prepare( ' AND created_at <= %s', $date_to . ' 23:59:59' );
		}

		// Get all return_products JSON and parse them.
		$returns = $wpdb->get_results(
			"SELECT return_products FROM {$this->table_name} WHERE {$where}", // phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared
			ARRAY_A
		);

		$product_counts = array();

		foreach ( $returns as $return ) {
			$skus = json_decode( $return['return_products'], true );
			if ( is_array( $skus ) ) {
				foreach ( $skus as $sku ) {
					if ( ! isset( $product_counts[ $sku ] ) ) {
						$product_counts[ $sku ] = 0;
					}
					++$product_counts[ $sku ];
				}
			}
		}

		// Sort by count descending.
		arsort( $product_counts );

		// Get top products with details.
		$top_products = array();
		$count        = 0;

		foreach ( $product_counts as $sku => $return_count ) {
			if ( $count >= $limit ) {
				break;
			}

			$product_id = wc_get_product_id_by_sku( $sku );
			$product    = $product_id ? wc_get_product( $product_id ) : null;

			$top_products[] = array(
				'sku'          => $sku,
				'name'         => $product ? $product->get_name() : __( 'Unknown Product', 'atlas-returns' ),
				'return_count' => $return_count,
				'product_id'   => $product_id,
				'edit_url'     => $product_id ? get_edit_post_link( $product_id ) : '',
			);

			++$count;
		}

		return $top_products;
	}

	/**
	 * Get recent returns.
	 *
	 * @param int $limit Number of returns to fetch.
	 * @return array Recent returns.
	 */
	public function get_recent_returns( $limit = 10 ) {
		global $wpdb;

		$results = $wpdb->get_results(
			$wpdb->prepare(
				"SELECT
                    id,
                    original_order_id,
                    return_order_id,
                    reason,
                    cost_difference,
                    created_at
                 FROM {$this->table_name}
                 ORDER BY created_at DESC
                 LIMIT %d",
				$limit
			),
			ARRAY_A
		);

		$reason_labels = CostCalculator::get_return_reasons( true );

		foreach ( $results as &$row ) {
			$row['reason_label']    = $reason_labels[ $row['reason'] ] ?? $row['reason'];
			$row['cost_difference'] = (float) $row['cost_difference'];
			$row['formatted_date']  = date_i18n( get_option( 'date_format' ), strtotime( $row['created_at'] ) );
			$row['original_url']    = admin_url( 'post.php?post=' . $row['original_order_id'] . '&action=edit' );
			$row['return_url']      = $row['return_order_id'] ? admin_url( 'post.php?post=' . $row['return_order_id'] . '&action=edit' ) : '';
		}

		return $results;
	}

	/**
	 * Get total WooCommerce orders count for return rate calculation.
	 *
	 * @param string $date_from Start date.
	 * @param string $date_to End date.
	 * @return int Order count.
	 */
	private function get_total_orders_count( $date_from, $date_to ) {
		$args = array(
			'date_created' => $date_from . '...' . $date_to,
			'status'       => array( 'completed', 'processing', 'on-hold' ),
			'return'       => 'ids',
			'limit'        => -1,
		);

		$orders = wc_get_orders( $args );

		return count( $orders );
	}

	/**
	 * Build date WHERE clause.
	 *
	 * @param string $date_from Start date.
	 * @param string $date_to End date.
	 * @return string WHERE clause.
	 */
	private function build_date_where( $date_from, $date_to ) {
		global $wpdb;

		$where = '1=1';

		if ( $date_from ) {
			$where .= $wpdb->prepare( ' AND created_at >= %s', $date_from . ' 00:00:00' );
		}

		if ( $date_to ) {
			$where .= $wpdb->prepare( ' AND created_at <= %s', $date_to . ' 23:59:59' );
		}

		return $where;
	}

	/**
	 * Fill date gaps for consistent charting.
	 *
	 * @param array  $results Query results.
	 * @param string $date_from Start date.
	 * @param string $date_to End date.
	 * @param string $group_by Grouping (day, week, month).
	 * @return array Filled results.
	 */
	private function fill_date_gaps( $results, $date_from, $date_to, $group_by ) {
		$filled   = array();
		$existing = array();

		foreach ( $results as $row ) {
			$existing[ $row['period'] ] = $row;
		}

		$current = strtotime( $date_from );
		$end     = strtotime( $date_to );

		while ( $current <= $end ) {
			switch ( $group_by ) {
				case 'day':
					$period  = gmdate( 'Y-m-d', $current );
					$label   = gmdate( 'M j', $current );
					$current = strtotime( '+1 day', $current );
					break;

				case 'week':
					$period  = gmdate( 'Y-W', $current );
					$label   = 'W' . gmdate( 'W', $current );
					$current = strtotime( '+1 week', $current );
					break;

				case 'month':
					$period  = gmdate( 'Y-m', $current );
					$label   = gmdate( 'M Y', $current );
					$current = strtotime( '+1 month', $current );
					break;

				default:
					$period  = gmdate( 'Y-m-d', $current );
					$label   = gmdate( 'M j', $current );
					$current = strtotime( '+1 day', $current );
			}

			if ( isset( $existing[ $period ] ) ) {
				$row          = $existing[ $period ];
				$row['label'] = $label;
			} else {
				$row = array(
					'period'     => $period,
					'label'      => $label,
					'count'      => 0,
					'total_cost' => 0,
					'refunded'   => 0,
					'charged'    => 0,
				);
			}

			$row['count']      = (int) $row['count'];
			$row['total_cost'] = (float) $row['total_cost'];
			$row['refunded']   = (float) ( $row['refunded'] ?? 0 );
			$row['charged']    = (float) ( $row['charged'] ?? 0 );

			$filled[] = $row;
		}

		return $filled;
	}
}
