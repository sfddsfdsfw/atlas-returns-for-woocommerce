<?php
/**
 * Cost calculator class.
 *
 * @package AtlasReturns\Core
 */

namespace AtlasReturns\Core;

/**
 * Class CostCalculator
 *
 * Calculates costs for return orders.
 */
class CostCalculator {

	/**
	 * Return reason: Customer fault.
	 */
	const REASON_CUSTOMER_FAULT = 'customer_fault';

	/**
	 * Return reason: Company fault with special handling.
	 */
	const REASON_COMPANY_FAULT = 'company_fault';

	/**
	 * Return reason: Company fault without special handling.
	 */
	const REASON_COMPANY_FAULT_NO_ADMIN = 'company_fault_no_special_admin';

	/**
	 * Shipping cost for customer fault returns.
	 *
	 * @var float
	 */
	private $shipping_cost;

	/**
	 * COD fee for customer fault returns.
	 *
	 * @var float
	 */
	private $cod_fee;

	/**
	 * Constructor.
	 */
	public function __construct() {
		$this->shipping_cost = (float) get_option( 'atlr_shipping_cost', 2.0 );
		$this->cod_fee       = (float) get_option( 'atlr_cod_fee', 1.0 );
	}

	/**
	 * Calculate cost difference for a return.
	 *
	 * @param \WC_Order $order Original order.
	 * @param string    $reason Return reason.
	 * @param array     $return_skus SKUs of products to return.
	 * @param array     $new_skus SKUs of new products.
	 * @return CalculationResult Calculation result.
	 */
	public function calculate( \WC_Order $order, $reason, array $return_skus, array $new_skus ) {
		$result = new CalculationResult();

		// Get order item prices indexed by SKU.
		$order_item_prices = $this->get_order_item_prices( $order );
		$order_skus        = array_keys( $order_item_prices );

		// Validate and add return products.
		foreach ( $return_skus as $sku ) {
			if ( empty( $sku ) ) {
				continue;
			}

			// Check if product exists in original order.
			if ( ! in_array( $sku, $order_skus, true ) ) {
				$result->add_error(
					sprintf(
						/* translators: %s: product SKU */
						__( 'Product with SKU %s is not in the original order.', 'atlas-returns' ),
						$sku
					)
				);
				continue;
			}

			// Get product.
			$product_id = wc_get_product_id_by_sku( $sku );
			if ( ! $product_id ) {
				$result->add_error(
					sprintf(
						/* translators: %s: product SKU */
						__( 'Product with SKU %s not found.', 'atlas-returns' ),
						$sku
					)
				);
				continue;
			}

			$product = wc_get_product( $product_id );
			$result->add_return_product(
				$product_id,
				$sku,
				$product->get_name(),
				$order_item_prices[ $sku ]
			);
		}

		// Validate and add new products.
		foreach ( $new_skus as $sku ) {
			if ( empty( $sku ) ) {
				continue;
			}

			$product_id = wc_get_product_id_by_sku( $sku );
			if ( ! $product_id ) {
				$result->add_error(
					sprintf(
						/* translators: %s: product SKU */
						__( 'Product with SKU %s not found.', 'atlas-returns' ),
						$sku
					)
				);
				continue;
			}

			$product = wc_get_product( $product_id );
			$result->add_new_product(
				$product_id,
				$sku,
				$product->get_name(),
				(float) $product->get_price(),
				(int) $product->get_stock_quantity()
			);
		}

		// Apply fees based on reason.
		if ( self::REASON_CUSTOMER_FAULT === $reason ) {
			$result->set_shipping_cost( $this->shipping_cost );
			$result->set_cod_fee( $this->cod_fee );
		}

		return $result;
	}

	/**
	 * Get order item prices indexed by SKU.
	 *
	 * @param \WC_Order $order Order object.
	 * @return array Prices indexed by SKU.
	 */
	private function get_order_item_prices( \WC_Order $order ) {
		$prices = array();

		foreach ( $order->get_items() as $item ) {
			$product = $item->get_product();
			if ( $product ) {
				$sku            = $product->get_sku();
				$quantity       = $item->get_quantity();
				$prices[ $sku ] = $item->get_total() / $quantity;
			}
		}

		return $prices;
	}

	/**
	 * Get available return reasons.
	 *
	 * @param bool $include_pro Include Pro-only reasons.
	 * @return array Return reasons.
	 */
	public static function get_return_reasons( $include_pro = true ) {
		$reasons = array(
			self::REASON_CUSTOMER_FAULT => __( 'Customer Fault', 'atlas-returns' ),
		);

		if ( $include_pro ) {
			$reasons[ self::REASON_COMPANY_FAULT ]          = __( 'Company Fault - Special Handling', 'atlas-returns' );
			$reasons[ self::REASON_COMPANY_FAULT_NO_ADMIN ] = __( 'Company Fault - No Special Handling', 'atlas-returns' );
		}

		return $reasons;
	}

	/**
	 * Get return reason descriptions.
	 *
	 * @return array Reason descriptions.
	 */
	public static function get_reason_descriptions() {
		return array(
			self::REASON_CUSTOMER_FAULT         => __( 'Customer made an error in their order. Customer is charged product difference, shipping, and COD fee.', 'atlas-returns' ),
			self::REASON_COMPANY_FAULT          => __( 'Company sent wrong product. Customer is not charged shipping or COD fee. Special handling for pickup on delivery.', 'atlas-returns' ),
			self::REASON_COMPANY_FAULT_NO_ADMIN => __( 'Customer received defective product. Replacement sent without pickup. No extra charges.', 'atlas-returns' ),
		);
	}
}
