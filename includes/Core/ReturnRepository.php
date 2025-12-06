<?php
/**
 * Return repository class.
 *
 * @package AtlasReturns\Core
 */

namespace AtlasReturns\Core;

/**
 * Class ReturnRepository
 *
 * Handles database operations for returns.
 *
 * @phpcs:disable WordPress.DB.DirectDatabaseQuery.DirectQuery
 * @phpcs:disable WordPress.DB.DirectDatabaseQuery.NoCaching
 */
class ReturnRepository {

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
	 * Save a return record.
	 *
	 * @param array $data Return data.
	 * @return int|false Insert ID or false on failure.
	 */
	public function save( array $data ) {
		global $wpdb;

		$defaults = array(
			'original_order_id' => 0,
			'return_order_id'   => null,
			'reason'            => '',
			'status'            => 'pending',
			'return_products'   => '[]',
			'new_products'      => '[]',
			'cost_difference'   => 0,
			'shipping_cost'     => 0,
			'cod_fee'           => 0,
			'coupon_id'         => null,
			'notes'             => null,
			'created_by'        => get_current_user_id(),
			'created_at'        => current_time( 'mysql' ),
			'updated_at'        => current_time( 'mysql' ),
		);

		$data = wp_parse_args( $data, $defaults );

		$result = $wpdb->insert(
			$this->table_name,
			$data,
			array(
				'%d', // original_order_id
				'%d', // return_order_id
				'%s', // reason
				'%s', // status
				'%s', // return_products
				'%s', // new_products
				'%f', // cost_difference
				'%f', // shipping_cost
				'%f', // cod_fee
				'%d', // coupon_id
				'%s', // notes
				'%d', // created_by
				'%s', // created_at
				'%s', // updated_at
			)
		);

		return $result ? $wpdb->insert_id : false;
	}

	/**
	 * Update a return record.
	 *
	 * @param int   $id Return ID.
	 * @param array $data Data to update.
	 * @return bool True on success.
	 */
	public function update( $id, array $data ) {
		global $wpdb;

		$data['updated_at'] = current_time( 'mysql' );

		$result = $wpdb->update(
			$this->table_name,
			$data,
			array( 'id' => $id ),
			null,
			array( '%d' )
		);

		return false !== $result;
	}

	/**
	 * Find a return by ID.
	 *
	 * @param int $id Return ID.
	 * @return object|null Return record or null.
	 */
	public function find( $id ) {
		global $wpdb;

		return $wpdb->get_row(
			$wpdb->prepare(
				"SELECT * FROM {$this->table_name} WHERE id = %d",
				$id
			)
		);
	}

	/**
	 * Find returns by original order ID.
	 *
	 * @param int $order_id Order ID.
	 * @return array Returns.
	 */
	public function find_by_order( $order_id ) {
		global $wpdb;

		return $wpdb->get_results(
			$wpdb->prepare(
				"SELECT * FROM {$this->table_name} WHERE original_order_id = %d ORDER BY created_at DESC",
				$order_id
			)
		);
	}

	/**
	 * Get all returns with pagination.
	 *
	 * @param int $page Page number.
	 * @param int $per_page Items per page.
	 * @return array Returns.
	 */
	public function get_all( $page = 1, $per_page = 20 ) {
		global $wpdb;

		$offset = ( $page - 1 ) * $per_page;

		return $wpdb->get_results(
			$wpdb->prepare(
				"SELECT * FROM {$this->table_name} ORDER BY created_at DESC LIMIT %d OFFSET %d",
				$per_page,
				$offset
			)
		);
	}

	/**
	 * Count total returns.
	 *
	 * @param array $filters Optional filters.
	 * @return int Total count.
	 */
	public function count( array $filters = array() ) {
		global $wpdb;

		$where = '1=1';

		if ( ! empty( $filters['reason'] ) ) {
			$where .= $wpdb->prepare( ' AND reason = %s', $filters['reason'] );
		}

		if ( ! empty( $filters['status'] ) ) {
			$where .= $wpdb->prepare( ' AND status = %s', $filters['status'] );
		}

		if ( ! empty( $filters['date_from'] ) ) {
			$where .= $wpdb->prepare( ' AND created_at >= %s', $filters['date_from'] );
		}

		if ( ! empty( $filters['date_to'] ) ) {
			$where .= $wpdb->prepare( ' AND created_at <= %s', $filters['date_to'] );
		}

		return (int) $wpdb->get_var( "SELECT COUNT(*) FROM {$this->table_name} WHERE {$where}" ); // phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared
	}

	/**
	 * Delete a return record.
	 *
	 * @param int $id Return ID.
	 * @return bool True on success.
	 */
	public function delete( $id ) {
		global $wpdb;

		$result = $wpdb->delete(
			$this->table_name,
			array( 'id' => $id ),
			array( '%d' )
		);

		return false !== $result;
	}

	/**
	 * Get returns by reason for analytics.
	 *
	 * @param string $date_from Start date (Y-m-d).
	 * @param string $date_to End date (Y-m-d).
	 * @return array Returns grouped by reason.
	 */
	public function get_by_reason( $date_from = null, $date_to = null ) {
		global $wpdb;

		$where = '1=1';

		if ( $date_from ) {
			$where .= $wpdb->prepare( ' AND created_at >= %s', $date_from . ' 00:00:00' );
		}

		if ( $date_to ) {
			$where .= $wpdb->prepare( ' AND created_at <= %s', $date_to . ' 23:59:59' );
		}

		return $wpdb->get_results(
			"SELECT reason, COUNT(*) as count, SUM(cost_difference) as total_cost
             FROM {$this->table_name}
             WHERE {$where}
             GROUP BY reason" // phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared
		);
	}

	/**
	 * Get returns trend for analytics.
	 *
	 * @param string $date_from Start date (Y-m-d).
	 * @param string $date_to End date (Y-m-d).
	 * @param string $group_by Group by (day, week, month).
	 * @return array Returns trend.
	 */
	public function get_trend( $date_from, $date_to, $group_by = 'day' ) {
		global $wpdb;

		$date_format = '%Y-%m-%d';
		if ( 'week' === $group_by ) {
			$date_format = '%Y-%u';
		} elseif ( 'month' === $group_by ) {
			$date_format = '%Y-%m';
		}

		return $wpdb->get_results(
			$wpdb->prepare(
				"SELECT DATE_FORMAT(created_at, %s) as period, COUNT(*) as count, SUM(cost_difference) as total_cost
                 FROM {$this->table_name}
                 WHERE created_at >= %s AND created_at <= %s
                 GROUP BY period
                 ORDER BY period ASC",
				$date_format,
				$date_from . ' 00:00:00',
				$date_to . ' 23:59:59'
			)
		);
	}

	/**
	 * Get summary statistics for analytics.
	 *
	 * @param string $date_from Start date (Y-m-d).
	 * @param string $date_to End date (Y-m-d).
	 * @return object Summary stats.
	 */
	public function get_summary( $date_from = null, $date_to = null ) {
		global $wpdb;

		$where = '1=1';

		if ( $date_from ) {
			$where .= $wpdb->prepare( ' AND created_at >= %s', $date_from . ' 00:00:00' );
		}

		if ( $date_to ) {
			$where .= $wpdb->prepare( ' AND created_at <= %s', $date_to . ' 23:59:59' );
		}

		return $wpdb->get_row(
			"SELECT
                COUNT(*) as total_returns,
                COALESCE(SUM(cost_difference), 0) as total_cost_difference,
                COALESCE(AVG(cost_difference), 0) as avg_cost_difference,
                COALESCE(SUM(CASE WHEN cost_difference < 0 THEN ABS(cost_difference) ELSE 0 END), 0) as total_refunded,
                COALESCE(SUM(CASE WHEN cost_difference > 0 THEN cost_difference ELSE 0 END), 0) as total_charged
             FROM {$this->table_name}
             WHERE {$where}" // phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared
		);
	}
}
