<?php
/**
 * AJAX handler class.
 *
 * @package AtlasReturns\Api
 */

namespace AtlasReturns\Api;

use AtlasReturns\Core\CostCalculator;
use AtlasReturns\Core\OrderCreator;
use AtlasReturns\Core\CouponHandler;
use AtlasReturns\Core\ReturnRepository;

/**
 * Class AjaxHandler
 *
 * Handles all AJAX requests for the plugin.
 */
class AjaxHandler {

	/**
	 * Constructor.
	 */
	public function __construct() {
		// Preview order.
		add_action( 'wp_ajax_atlr_preview_order', array( $this, 'preview_order' ) );

		// Calculate return.
		add_action( 'wp_ajax_atlr_calculate_return', array( $this, 'calculate_return' ) );

		// Create return order.
		add_action( 'wp_ajax_atlr_create_return', array( $this, 'create_return' ) );

		// Get return history.
		add_action( 'wp_ajax_atlr_get_history', array( $this, 'get_history' ) );
	}

	/**
	 * Verify AJAX request.
	 *
	 * @return bool True if valid, sends error and exits otherwise.
	 */
	private function verify_request() {
		// Check nonce.
		if ( ! check_ajax_referer( 'atlr_nonce', 'nonce', false ) ) {
			wp_send_json_error( __( 'Security check failed.', 'atlas-returns-for-woocommerce' ) );
		}

		// Check capability.
		if ( ! current_user_can( 'manage_atlas_returns' ) ) {
			wp_send_json_error( __( 'Permission denied.', 'atlas-returns-for-woocommerce' ) );
		}

		return true;
	}

	/**
	 * Check monthly limit for free version.
	 *
	 * @return bool True if within limit, false if exceeded.
	 */
	private function check_monthly_limit() {
		// Pro users have no limit.
		if ( defined( 'ATLR_PRO' ) && ATLR_PRO ) {
			return true;
		}

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

		return $count < $limit;
	}

	/**
	 * Increment monthly return count.
	 */
	private function increment_monthly_count() {
		$count = (int) get_option( 'atlr_monthly_returns_count', 0 );
		update_option( 'atlr_monthly_returns_count', $count + 1 );
	}

	/**
	 * Preview order AJAX handler.
	 */
	public function preview_order() {
		$this->verify_request();

		// phpcs:ignore WordPress.Security.NonceVerification.Missing -- Nonce verified in verify_request().
		$identifier = isset( $_POST['order_id'] ) ? sanitize_text_field( wp_unslash( $_POST['order_id'] ) ) : '';

		if ( empty( $identifier ) ) {
			wp_send_json_error( __( 'Please enter an order ID or phone number.', 'atlas-returns-for-woocommerce' ) );
		}

		$order = $this->get_order_by_identifier( $identifier );

		if ( ! $order ) {
			wp_send_json_error( __( 'Order not found.', 'atlas-returns-for-woocommerce' ) );
		}

		// Build preview HTML.
		$preview = $this->build_order_preview( $order );

		wp_send_json_success( $preview );
	}

	/**
	 * Calculate return AJAX handler.
	 */
	public function calculate_return() {
		$this->verify_request();

		// phpcs:disable WordPress.Security.NonceVerification.Missing -- Nonce verified in verify_request().
		$identifier          = isset( $_POST['order_id'] ) ? sanitize_text_field( wp_unslash( $_POST['order_id'] ) ) : '';
		$reason              = isset( $_POST['reason'] ) ? sanitize_text_field( wp_unslash( $_POST['reason'] ) ) : '';
		$products_to_replace = isset( $_POST['products_to_replace'] ) ? sanitize_text_field( wp_unslash( $_POST['products_to_replace'] ) ) : '';
		$new_products        = isset( $_POST['new_products'] ) ? sanitize_text_field( wp_unslash( $_POST['new_products'] ) ) : '';
		$validate            = isset( $_POST['validate'] ) ? filter_var( wp_unslash( $_POST['validate'] ), FILTER_VALIDATE_BOOLEAN ) : true;
		// phpcs:enable WordPress.Security.NonceVerification.Missing

		$order = $this->get_order_by_identifier( $identifier );

		if ( ! $order ) {
			wp_send_json_error( __( 'Order not found.', 'atlas-returns-for-woocommerce' ) );
		}

		// Parse SKUs.
		$return_skus = array_filter( array_map( 'trim', explode( ',', $products_to_replace ) ) );
		$new_skus    = array_filter( array_map( 'trim', explode( ',', $new_products ) ) );

		// Calculate costs.
		$calculator = new CostCalculator();
		$result     = $calculator->calculate( $order, $reason, $return_skus, $new_skus );

		// Check for errors if validation is enabled.
		if ( $validate && $result->has_errors() ) {
			wp_send_json_error( implode( '<br>', $result->get_errors() ) );
		}

		// Build calculation HTML.
		$html = $this->build_calculation_html( $result );

		wp_send_json_success( $html );
	}

	/**
	 * Create return order AJAX handler.
	 */
	public function create_return() {
		$this->verify_request();

		// Check monthly limit.
		if ( ! $this->check_monthly_limit() ) {
			$upgrade_url = function_exists( 'atlr_fs' ) ? atlr_fs()->get_upgrade_url() : admin_url( 'admin.php?page=atlas-returns-pricing' );
			wp_send_json_error(
				sprintf(
					/* translators: %s: upgrade URL */
					__( 'Monthly return limit reached. <a href="%s" target="_blank">Upgrade to Pro</a> for unlimited returns.', 'atlas-returns-for-woocommerce' ),
					esc_url( $upgrade_url )
				)
			);
		}

		// phpcs:disable WordPress.Security.NonceVerification.Missing -- Nonce verified in verify_request().
		$identifier          = isset( $_POST['order_id'] ) ? sanitize_text_field( wp_unslash( $_POST['order_id'] ) ) : '';
		$reason              = isset( $_POST['reason'] ) ? sanitize_text_field( wp_unslash( $_POST['reason'] ) ) : '';
		$products_to_replace = isset( $_POST['products_to_replace'] ) ? sanitize_text_field( wp_unslash( $_POST['products_to_replace'] ) ) : '';
		$new_products        = isset( $_POST['new_products'] ) ? sanitize_text_field( wp_unslash( $_POST['new_products'] ) ) : '';
		$create_coupon       = isset( $_POST['create_coupon'] ) ? filter_var( wp_unslash( $_POST['create_coupon'] ), FILTER_VALIDATE_BOOLEAN ) : false;
		// phpcs:enable WordPress.Security.NonceVerification.Missing

		// Validate inputs.
		if ( empty( $reason ) ) {
			wp_send_json_error( __( 'Please select a return reason.', 'atlas-returns-for-woocommerce' ) );
		}

		if ( empty( $products_to_replace ) ) {
			wp_send_json_error( __( 'Please enter products to return.', 'atlas-returns-for-woocommerce' ) );
		}

		if ( empty( $new_products ) ) {
			wp_send_json_error( __( 'Please enter new products.', 'atlas-returns-for-woocommerce' ) );
		}

		// Check reason availability for free version.
		if ( ! defined( 'ATLR_PRO' ) || ! ATLR_PRO ) {
			if ( $reason !== CostCalculator::REASON_CUSTOMER_FAULT ) {
				$upgrade_url = function_exists( 'atlr_fs' ) ? atlr_fs()->get_upgrade_url() : admin_url( 'admin.php?page=atlas-returns-pricing' );
				wp_send_json_error(
					sprintf(
						/* translators: %s: upgrade URL */
						__( 'This return reason requires Pro. <a href="%s" target="_blank">Upgrade now</a>.', 'atlas-returns-for-woocommerce' ),
						esc_url( $upgrade_url )
					)
				);
			}
		}

		$order = $this->get_order_by_identifier( $identifier );

		if ( ! $order ) {
			wp_send_json_error( __( 'Order not found.', 'atlas-returns-for-woocommerce' ) );
		}

		// Parse SKUs.
		$return_skus = array_filter( array_map( 'trim', explode( ',', $products_to_replace ) ) );
		$new_skus    = array_filter( array_map( 'trim', explode( ',', $new_products ) ) );

		// Calculate costs.
		$calculator = new CostCalculator();
		$result     = $calculator->calculate( $order, $reason, $return_skus, $new_skus );

		if ( $result->has_errors() ) {
			wp_send_json_error( implode( '<br>', $result->get_errors() ) );
		}

		// Create return order.
		$order_creator = new OrderCreator();
		$new_order     = $order_creator->create( $order, $reason, $result );

		if ( is_wp_error( $new_order ) ) {
			wp_send_json_error( $new_order->get_error_message() );
		}

		// Handle coupon creation if needed.
		$coupon_id = null;
		if ( $result->get_total_cost_difference() < 0 && $create_coupon ) {
			$coupon_handler = new CouponHandler();
			$coupon_id      = $coupon_handler->create_coupon( $order, abs( $result->get_total_cost_difference() ) );

			// Send coupon email if enabled.
			if ( 'yes' === get_option( 'atlr_enable_email_notifications', 'yes' ) ) {
				$coupon_handler->send_coupon_email( $order, $coupon_id );
			}
		}

		// Save return record.
		$repository = new ReturnRepository();
		$repository->save(
			array(
				'original_order_id' => $order->get_id(),
				'return_order_id'   => $new_order->get_id(),
				'reason'            => $reason,
				'status'            => 'completed',
				'return_products'   => wp_json_encode( $return_skus ),
				'new_products'      => wp_json_encode( $new_skus ),
				'cost_difference'   => $result->get_total_cost_difference(),
				'shipping_cost'     => $result->get_shipping_cost(),
				'cod_fee'           => $result->get_cod_fee(),
				'coupon_id'         => $coupon_id,
				'created_by'        => get_current_user_id(),
			)
		);

		// Increment monthly count.
		$this->increment_monthly_count();

		// Build success message.
		$message = sprintf(
			/* translators: %d: new order ID */
			__( 'Return order created successfully. Order ID: %d', 'atlas-returns-for-woocommerce' ),
			$new_order->get_id()
		);

		if ( $result->get_total_cost_difference() < 0 ) {
			$refund_amount = abs( $result->get_total_cost_difference() );
			if ( $create_coupon ) {
				$message .= ' ' . sprintf(
					/* translators: %s: coupon amount */
					__( 'A coupon for %s has been created and emailed to the customer.', 'atlas-returns-for-woocommerce' ),
					wc_price( $refund_amount )
				);
			} else {
				$message .= ' ' . sprintf(
					/* translators: %s: refund amount */
					__( 'You need to refund %s to the customer.', 'atlas-returns-for-woocommerce' ),
					wc_price( $refund_amount )
				);
			}
		}

		wp_send_json_success( wp_strip_all_tags( $message ) );
	}

	/**
	 * Get return history AJAX handler.
	 */
	public function get_history() {
		$this->verify_request();

		// phpcs:disable WordPress.Security.NonceVerification.Missing -- Nonce verified in verify_request().
		$page     = isset( $_POST['page'] ) ? absint( $_POST['page'] ) : 1;
		$per_page = isset( $_POST['per_page'] ) ? absint( $_POST['per_page'] ) : 20;
		// phpcs:enable WordPress.Security.NonceVerification.Missing

		$repository = new ReturnRepository();
		$returns    = $repository->get_all( $page, $per_page );
		$total      = $repository->count();

		wp_send_json_success(
			array(
				'returns' => $returns,
				'total'   => $total,
				'pages'   => ceil( $total / $per_page ),
			)
		);
	}

	/**
	 * Get order by identifier (ID or phone number).
	 *
	 * @param string $identifier Order ID or phone number.
	 * @return \WC_Order|false Order object or false if not found.
	 */
	private function get_order_by_identifier( $identifier ) {
		// If numeric and 7 digits or less, treat as order ID.
		if ( is_numeric( $identifier ) && strlen( $identifier ) <= 7 ) {
			return wc_get_order( $identifier );
		}

		// Otherwise, search by phone number.
		// phpcs:disable WordPress.DB.SlowDBQuery.slow_db_query_meta_key,WordPress.DB.SlowDBQuery.slow_db_query_meta_value -- Required to find orders by phone.
		$args = array(
			'meta_key'    => '_billing_phone',
			'meta_value'  => $identifier,
			'post_type'   => 'shop_order',
			'post_status' => 'any',
			'numberposts' => 1,
			'orderby'     => 'date',
			'order'       => 'DESC',
		);
		// phpcs:enable WordPress.DB.SlowDBQuery.slow_db_query_meta_key,WordPress.DB.SlowDBQuery.slow_db_query_meta_value

		// Support HPOS.
		if ( class_exists( '\Automattic\WooCommerce\Utilities\OrderUtil' ) &&
			\Automattic\WooCommerce\Utilities\OrderUtil::custom_orders_table_usage_is_enabled() ) {
			$orders = wc_get_orders(
				array(
					'billing_phone' => $identifier,
					'limit'         => 1,
					'orderby'       => 'date',
					'order'         => 'DESC',
				)
			);
			return ! empty( $orders ) ? $orders[0] : false;
		}

		$orders = get_posts( $args );
		return ! empty( $orders ) ? wc_get_order( $orders[0]->ID ) : false;
	}

	/**
	 * Build order preview HTML.
	 *
	 * @param \WC_Order $order Order object.
	 * @return string HTML preview.
	 */
	private function build_order_preview( $order ) {
		$order_date     = $order->get_date_created();
		$formatted_date = $order_date ? $order_date->date_i18n( 'd-m-Y' ) : '';

		// Calculate days since order.
		$days_ago = 0;
		if ( $order_date ) {
			$now      = new \DateTime();
			$created  = new \DateTime( $order_date->date( 'Y-m-d' ) );
			$days_ago = $now->diff( $created )->days;
		}

		$warning_class = $days_ago >= 20 ? 'atlr-warning' : 'atlr-success';
		$days_text     = sprintf(
			/* translators: %d: number of days */
			_n( '%d day ago', '%d days ago', $days_ago, 'atlas-returns-for-woocommerce' ),
			$days_ago
		);

		if ( $days_ago >= 20 ) {
			$days_text = __( 'WARNING', 'atlas-returns-for-woocommerce' ) . ' - ' . $days_text;
		}

		ob_start();
		?>
		<div class="atlr-order-info">
			<p>
				<strong><?php esc_html_e( 'Order ID:', 'atlas-returns-for-woocommerce' ); ?></strong> <?php echo esc_html( $order->get_id() ); ?> |
				<strong><?php esc_html_e( 'Name:', 'atlas-returns-for-woocommerce' ); ?></strong> <?php echo esc_html( $order->get_billing_first_name() . ' ' . $order->get_billing_last_name() ); ?> |
				<strong><?php esc_html_e( 'Phone:', 'atlas-returns-for-woocommerce' ); ?></strong> <?php echo esc_html( $order->get_billing_phone() ); ?>
			</p>
			<p>
				<strong><?php esc_html_e( 'Address:', 'atlas-returns-for-woocommerce' ); ?></strong> <?php echo esc_html( $order->get_billing_address_1() ); ?> |
				<strong><?php esc_html_e( 'City:', 'atlas-returns-for-woocommerce' ); ?></strong> <?php echo esc_html( $order->get_billing_city() ); ?> |
				<strong><?php esc_html_e( 'Postcode:', 'atlas-returns-for-woocommerce' ); ?></strong> <?php echo esc_html( $order->get_billing_postcode() ); ?>
			</p>
			<p>
				<strong><?php esc_html_e( 'Order Date:', 'atlas-returns-for-woocommerce' ); ?></strong> <?php echo esc_html( $formatted_date ); ?>
				<span class="<?php echo esc_attr( $warning_class ); ?>">(<?php echo esc_html( $days_text ); ?>)</span>
			</p>
			<p>
				<strong><?php esc_html_e( 'Status:', 'atlas-returns-for-woocommerce' ); ?></strong> <?php echo esc_html( wc_get_order_status_name( $order->get_status() ) ); ?> |
				<strong><?php esc_html_e( 'Payment:', 'atlas-returns-for-woocommerce' ); ?></strong> <?php echo esc_html( $order->get_payment_method_title() ); ?> |
				<strong><?php esc_html_e( 'Shipping:', 'atlas-returns-for-woocommerce' ); ?></strong> <?php echo esc_html( $order->get_shipping_method() ); ?>
			</p>
		</div>

		<h3><?php esc_html_e( 'Order Items', 'atlas-returns-for-woocommerce' ); ?></h3>
		<ul class="atlr-order-items">
			<?php foreach ( $order->get_items() as $item ) : ?>
				<?php
				$product = $item->get_product();
				if ( ! $product ) {
					continue;
				}
				$quantity = $item->get_quantity();
				$price    = $item->get_total() / $quantity;
				?>
				<li>
					<?php echo esc_html( $product->get_name() ); ?>
					(SKU: <strong><?php echo esc_html( $product->get_sku() ); ?></strong>) -
					<?php echo wp_kses_post( wc_price( $price ) ); ?> x <?php echo esc_html( $quantity ); ?>
				</li>
			<?php endforeach; ?>
		</ul>
		<?php
		return ob_get_clean();
	}

	/**
	 * Build calculation HTML.
	 *
	 * @param \AtlasReturns\Core\CalculationResult $result Calculation result.
	 * @return string HTML.
	 */
	private function build_calculation_html( $result ) {
		ob_start();
		?>
		<h3><?php esc_html_e( 'Products to Return:', 'atlas-returns-for-woocommerce' ); ?></h3>
		<ul class="atlr-return-products">
			<?php foreach ( $result->get_return_products() as $product_data ) : ?>
				<li class="atlr-warning-text">
					<?php echo esc_html( $product_data['name'] ); ?>
					(SKU: <?php echo esc_html( $product_data['sku'] ); ?>) -
					<?php echo wp_kses_post( wc_price( $product_data['price'] ) ); ?>
				</li>
			<?php endforeach; ?>
		</ul>

		<h3><?php esc_html_e( 'New Products:', 'atlas-returns-for-woocommerce' ); ?></h3>
		<ul class="atlr-new-products">
			<?php foreach ( $result->get_new_products() as $product_data ) : ?>
				<?php
				$stock_class = $product_data['stock'] <= 0 ? 'atlr-out-of-stock' : '';
				?>
				<li class="atlr-success-text <?php echo esc_attr( $stock_class ); ?>">
					<?php echo esc_html( $product_data['name'] ); ?>
					(SKU: <?php echo esc_html( $product_data['sku'] ); ?>) -
					<?php echo wp_kses_post( wc_price( $product_data['price'] ) ); ?> -
					<span class="atlr-stock">
						(<?php esc_html_e( 'Stock:', 'atlas-returns-for-woocommerce' ); ?> <?php echo esc_html( $product_data['stock'] ); ?>)
					</span>
				</li>
			<?php endforeach; ?>
		</ul>

		<h3><?php esc_html_e( 'Cost Breakdown:', 'atlas-returns-for-woocommerce' ); ?></h3>
		<div class="atlr-cost-breakdown">
			<p>
				<strong><?php esc_html_e( 'Total Cost of Returned Products:', 'atlas-returns-for-woocommerce' ); ?></strong>
				<?php echo wp_kses_post( wc_price( $result->get_total_return_cost() ) ); ?>
			</p>
			<p>
				<strong><?php esc_html_e( 'Total Cost of New Products:', 'atlas-returns-for-woocommerce' ); ?></strong>
				<?php echo wp_kses_post( wc_price( $result->get_total_new_cost() ) ); ?>
			</p>
			<?php if ( $result->get_shipping_cost() > 0 ) : ?>
				<p>
					<strong><?php esc_html_e( 'Shipping Cost:', 'atlas-returns-for-woocommerce' ); ?></strong>
					<?php echo wp_kses_post( wc_price( $result->get_shipping_cost() ) ); ?>
				</p>
			<?php endif; ?>
			<?php if ( $result->get_cod_fee() > 0 ) : ?>
				<p>
					<strong><?php esc_html_e( 'COD Fee:', 'atlas-returns-for-woocommerce' ); ?></strong>
					<?php echo wp_kses_post( wc_price( $result->get_cod_fee() ) ); ?>
				</p>
			<?php endif; ?>
			<p class="atlr-total-difference">
				<strong><?php esc_html_e( 'Total Cost Difference:', 'atlas-returns-for-woocommerce' ); ?></strong>
				<?php echo wp_kses_post( wc_price( $result->get_total_cost_difference() ) ); ?>
			</p>
		</div>

		<?php if ( $result->get_total_cost_difference() < 0 ) : ?>
			<div class="atlr-coupon-option">
				<label>
					<input type="checkbox" id="atlr_create_coupon" name="create_coupon" value="1" />
					<?php esc_html_e( 'Create coupon for remaining credit?', 'atlas-returns-for-woocommerce' ); ?>
				</label>
			</div>
		<?php endif; ?>
		<?php
		return ob_get_clean();
	}
}
