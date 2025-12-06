<?php
/**
 * Order creator class.
 *
 * @package AtlasReturns\Core
 */

namespace AtlasReturns\Core;

/**
 * Class OrderCreator
 *
 * Creates WooCommerce orders for returns.
 */
class OrderCreator {

	/**
	 * Create a return order.
	 *
	 * @param \WC_Order         $original_order Original order.
	 * @param string            $reason Return reason.
	 * @param CalculationResult $calculation Calculation result.
	 * @return \WC_Order|\WP_Error New order or error.
	 */
	public function create( \WC_Order $original_order, $reason, CalculationResult $calculation ) {
		try {
			$new_order = wc_create_order();

			if ( is_wp_error( $new_order ) ) {
				return $new_order;
			}

			// Set order status.
			$new_order->set_status( 'processing' );

			// Set payment method.
			$payment_method = get_option( 'atlr_default_payment_method', 'cod' );
			$new_order->set_payment_method( $payment_method );

			// Set payment method title based on method.
			$payment_titles = array(
				'cod'  => __( 'Cash on Delivery', 'atlas-returns' ),
				'bacs' => __( 'Bank Transfer', 'atlas-returns' ),
			);
			$new_order->set_payment_method_title(
				isset( $payment_titles[ $payment_method ] ) ? $payment_titles[ $payment_method ] : $payment_method
			);

			// Copy addresses from original order.
			$new_order->set_address( $original_order->get_address( 'billing' ), 'billing' );
			$new_order->set_address( $original_order->get_address( 'shipping' ), 'shipping' );

			// Add customer note for special handling.
			if ( CostCalculator::REASON_COMPANY_FAULT_NO_ADMIN !== $reason ) {
				$special_note = get_option( 'atlr_special_handling_note', __( 'SPECIAL HANDLING - PICKUP ON DELIVERY', 'atlas-returns' ) );
				$new_order->set_customer_note( '*** ' . $special_note . ' ***' );
			}

			// Add shipping item.
			$shipping_item = new \WC_Order_Item_Shipping();
			$shipping_item->set_method_title( __( 'Return Shipping', 'atlas-returns' ) );
			$shipping_item->set_method_id( 'atlas_returns_shipping' );

			// Set shipping cost (0 if customer owes nothing or has credit).
			$shipping_cost = $calculation->get_total_cost_difference() < 0 ? 0 : $calculation->get_shipping_cost();
			$shipping_item->set_total( $shipping_cost );
			$new_order->add_item( $shipping_item );

			// Add COD fee if applicable.
			$cod_fee = $calculation->get_total_cost_difference() < 0 ? 0 : $calculation->get_cod_fee();
			if ( $cod_fee > 0 ) {
				$this->add_fee( $new_order, __( 'COD Fee', 'atlas-returns' ), $cod_fee );
			}

			// Add new products.
			$total_return_cost = $calculation->get_total_return_cost();
			$new_products      = $calculation->get_new_products();
			$product_count     = count( $new_products );

			foreach ( $new_products as $product_data ) {
				$product = wc_get_product( $product_data['id'] );

				if ( ! $product ) {
					continue;
				}

				$item = new \WC_Order_Item_Product();
				$item->set_product( $product );
				$item->set_quantity( 1 );

				// Calculate adjusted price.
				$original_price = $product_data['price'];

				if ( $calculation->get_total_cost_difference() < 0 ) {
					// Customer has credit - set price to 0.
					$adjusted_price = 0;
				} else {
					// Distribute the return credit across products.
					$credit_per_product = $total_return_cost / $product_count;
					$adjusted_price     = max( 0, $original_price - $credit_per_product );
				}

				$item->set_subtotal( $adjusted_price );
				$item->set_total( $adjusted_price );

				$new_order->add_item( $item );
			}

			// Calculate totals.
			$new_order->calculate_totals();

			// If customer has credit, set total to 0.
			if ( $calculation->get_total_cost_difference() < 0 ) {
				$new_order->set_total( 0 );
			}

			// Add order meta.
			$new_order->update_meta_data( '_atlr_return_order', 'yes' );
			$new_order->update_meta_data( '_atlr_original_order_id', $original_order->get_id() );
			$new_order->update_meta_data( '_atlr_return_reason', $reason );
			$new_order->update_meta_data( '_atlr_cost_difference', $calculation->get_total_cost_difference() );

			// Add order note.
			$new_order->add_order_note(
				sprintf(
					/* translators: %d: original order ID */
					__( 'Return order created from order #%d via Atlas Returns.', 'atlas-returns' ),
					$original_order->get_id()
				)
			);

			// Save order.
			$new_order->save();

			// Add note to original order.
			$original_order->add_order_note(
				sprintf(
					/* translators: %d: return order ID */
					__( 'Return order #%d created via Atlas Returns.', 'atlas-returns' ),
					$new_order->get_id()
				)
			);

			/**
			 * Action fired after a return order is created.
			 *
			 * @param \WC_Order         $new_order New return order.
			 * @param \WC_Order         $original_order Original order.
			 * @param string            $reason Return reason.
			 * @param CalculationResult $calculation Calculation result.
			 */
			do_action( 'atlr_return_order_created', $new_order, $original_order, $reason, $calculation );

			return $new_order;

		} catch ( \Exception $e ) {
			return new \WP_Error( 'order_creation_failed', $e->getMessage() );
		}
	}

	/**
	 * Add a fee to an order.
	 *
	 * @param \WC_Order $order Order object.
	 * @param string    $name Fee name.
	 * @param float     $amount Fee amount.
	 */
	private function add_fee( \WC_Order $order, $name, $amount ) {
		$fee = new \WC_Order_Item_Fee();
		$fee->set_name( $name );
		$fee->set_amount( $amount );
		$fee->set_tax_class( '' );
		$fee->set_tax_status( 'none' );
		$fee->set_total( $amount );

		$order->add_item( $fee );
	}
}
