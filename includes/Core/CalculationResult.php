<?php
/**
 * Calculation result class.
 *
 * @package AtlasReturns\Core
 */

namespace AtlasReturns\Core;

/**
 * Class CalculationResult
 *
 * Holds the results of a return cost calculation.
 */
class CalculationResult {

	/**
	 * Products being returned with their data.
	 *
	 * @var array
	 */
	private $return_products = array();

	/**
	 * New products with their data.
	 *
	 * @var array
	 */
	private $new_products = array();

	/**
	 * Total cost of returned products.
	 *
	 * @var float
	 */
	private $total_return_cost = 0.0;

	/**
	 * Total cost of new products.
	 *
	 * @var float
	 */
	private $total_new_cost = 0.0;

	/**
	 * Shipping cost.
	 *
	 * @var float
	 */
	private $shipping_cost = 0.0;

	/**
	 * COD fee.
	 *
	 * @var float
	 */
	private $cod_fee = 0.0;

	/**
	 * Errors encountered during calculation.
	 *
	 * @var array
	 */
	private $errors = array();

	/**
	 * Add a return product.
	 *
	 * @param int    $product_id Product ID.
	 * @param string $sku Product SKU.
	 * @param string $name Product name.
	 * @param float  $price Price from original order.
	 */
	public function add_return_product( $product_id, $sku, $name, $price ) {
		$this->return_products[]  = array(
			'id'    => $product_id,
			'sku'   => $sku,
			'name'  => $name,
			'price' => $price,
		);
		$this->total_return_cost += $price;
	}

	/**
	 * Add a new product.
	 *
	 * @param int    $product_id Product ID.
	 * @param string $sku Product SKU.
	 * @param string $name Product name.
	 * @param float  $price Current product price.
	 * @param int    $stock Stock quantity.
	 */
	public function add_new_product( $product_id, $sku, $name, $price, $stock ) {
		$this->new_products[]  = array(
			'id'    => $product_id,
			'sku'   => $sku,
			'name'  => $name,
			'price' => $price,
			'stock' => $stock,
		);
		$this->total_new_cost += $price;
	}

	/**
	 * Set shipping cost.
	 *
	 * @param float $cost Shipping cost.
	 */
	public function set_shipping_cost( $cost ) {
		$this->shipping_cost = (float) $cost;
	}

	/**
	 * Set COD fee.
	 *
	 * @param float $fee COD fee.
	 */
	public function set_cod_fee( $fee ) {
		$this->cod_fee = (float) $fee;
	}

	/**
	 * Add an error.
	 *
	 * @param string $error Error message.
	 */
	public function add_error( $error ) {
		$this->errors[] = $error;
	}

	/**
	 * Check if there are errors.
	 *
	 * @return bool True if errors exist.
	 */
	public function has_errors() {
		return ! empty( $this->errors );
	}

	/**
	 * Get errors.
	 *
	 * @return array Errors.
	 */
	public function get_errors() {
		return $this->errors;
	}

	/**
	 * Get return products.
	 *
	 * @return array Return products.
	 */
	public function get_return_products() {
		return $this->return_products;
	}

	/**
	 * Get new products.
	 *
	 * @return array New products.
	 */
	public function get_new_products() {
		return $this->new_products;
	}

	/**
	 * Get total return cost.
	 *
	 * @return float Total return cost.
	 */
	public function get_total_return_cost() {
		return $this->total_return_cost;
	}

	/**
	 * Get total new cost.
	 *
	 * @return float Total new cost.
	 */
	public function get_total_new_cost() {
		return $this->total_new_cost;
	}

	/**
	 * Get shipping cost.
	 *
	 * @return float Shipping cost.
	 */
	public function get_shipping_cost() {
		return $this->shipping_cost;
	}

	/**
	 * Get COD fee.
	 *
	 * @return float COD fee.
	 */
	public function get_cod_fee() {
		return $this->cod_fee;
	}

	/**
	 * Get total cost difference.
	 *
	 * New products + shipping + COD - returned products.
	 *
	 * @return float Cost difference (positive = customer pays, negative = refund/coupon).
	 */
	public function get_total_cost_difference() {
		return $this->total_new_cost + $this->shipping_cost + $this->cod_fee - $this->total_return_cost;
	}

	/**
	 * Get return product IDs.
	 *
	 * @return array Product IDs.
	 */
	public function get_return_product_ids() {
		return array_column( $this->return_products, 'id' );
	}

	/**
	 * Get new product IDs.
	 *
	 * @return array Product IDs.
	 */
	public function get_new_product_ids() {
		return array_column( $this->new_products, 'id' );
	}
}
