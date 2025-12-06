<?php
/**
 * Unit tests for CalculationResult class.
 *
 * @package AtlasReturns\Tests\Unit
 */

namespace AtlasReturns\Tests\Unit;

use AtlasReturns\Tests\TestCase;
use AtlasReturns\Core\CalculationResult;

/**
 * Class CalculationResultTest
 *
 * Tests for the CalculationResult class.
 */
class CalculationResultTest extends TestCase {

    /**
     * @var CalculationResult
     */
    private $result;

    /**
     * Set up test fixtures.
     */
    protected function setUp(): void {
        parent::setUp();
        $this->result = new CalculationResult();
    }

    /**
     * Test initial state has no errors.
     */
    public function test_initial_state_has_no_errors() {
        $this->assertFalse( $this->result->has_errors() );
        $this->assertEmpty( $this->result->get_errors() );
    }

    /**
     * Test initial state has no products.
     */
    public function test_initial_state_has_no_products() {
        $this->assertEmpty( $this->result->get_return_products() );
        $this->assertEmpty( $this->result->get_new_products() );
    }

    /**
     * Test initial costs are zero.
     */
    public function test_initial_costs_are_zero() {
        $this->assertEquals( 0.0, $this->result->get_total_return_cost() );
        $this->assertEquals( 0.0, $this->result->get_total_new_cost() );
        $this->assertEquals( 0.0, $this->result->get_shipping_cost() );
        $this->assertEquals( 0.0, $this->result->get_cod_fee() );
        $this->assertEquals( 0.0, $this->result->get_total_cost_difference() );
    }

    /**
     * Test adding a return product.
     */
    public function test_add_return_product() {
        $this->result->add_return_product( 1, 'SKU-001', 'Test Product', 25.00 );

        $products = $this->result->get_return_products();

        $this->assertCount( 1, $products );
        $this->assertEquals( 1, $products[0]['id'] );
        $this->assertEquals( 'SKU-001', $products[0]['sku'] );
        $this->assertEquals( 'Test Product', $products[0]['name'] );
        $this->assertEquals( 25.00, $products[0]['price'] );
    }

    /**
     * Test adding multiple return products accumulates cost.
     */
    public function test_add_multiple_return_products_accumulates_cost() {
        $this->result->add_return_product( 1, 'SKU-001', 'Product 1', 25.00 );
        $this->result->add_return_product( 2, 'SKU-002', 'Product 2', 15.00 );

        $this->assertCount( 2, $this->result->get_return_products() );
        $this->assertEquals( 40.00, $this->result->get_total_return_cost() );
    }

    /**
     * Test adding a new product.
     */
    public function test_add_new_product() {
        $this->result->add_new_product( 3, 'SKU-003', 'New Product', 30.00, 10 );

        $products = $this->result->get_new_products();

        $this->assertCount( 1, $products );
        $this->assertEquals( 3, $products[0]['id'] );
        $this->assertEquals( 'SKU-003', $products[0]['sku'] );
        $this->assertEquals( 'New Product', $products[0]['name'] );
        $this->assertEquals( 30.00, $products[0]['price'] );
        $this->assertEquals( 10, $products[0]['stock'] );
    }

    /**
     * Test adding multiple new products accumulates cost.
     */
    public function test_add_multiple_new_products_accumulates_cost() {
        $this->result->add_new_product( 1, 'SKU-001', 'Product 1', 20.00, 5 );
        $this->result->add_new_product( 2, 'SKU-002', 'Product 2', 35.00, 8 );

        $this->assertCount( 2, $this->result->get_new_products() );
        $this->assertEquals( 55.00, $this->result->get_total_new_cost() );
    }

    /**
     * Test setting shipping cost.
     */
    public function test_set_shipping_cost() {
        $this->result->set_shipping_cost( 2.50 );

        $this->assertEquals( 2.50, $this->result->get_shipping_cost() );
    }

    /**
     * Test setting COD fee.
     */
    public function test_set_cod_fee() {
        $this->result->set_cod_fee( 1.80 );

        $this->assertEquals( 1.80, $this->result->get_cod_fee() );
    }

    /**
     * Test cost difference calculation - customer pays more.
     */
    public function test_cost_difference_customer_pays_more() {
        // Return product worth €20.
        $this->result->add_return_product( 1, 'SKU-001', 'Old Product', 20.00 );
        // New product costs €35.
        $this->result->add_new_product( 2, 'SKU-002', 'New Product', 35.00, 10 );
        // Add shipping and COD.
        $this->result->set_shipping_cost( 2.00 );
        $this->result->set_cod_fee( 1.00 );

        // Difference = 35 + 2 + 1 - 20 = 18.
        $this->assertEquals( 18.00, $this->result->get_total_cost_difference() );
    }

    /**
     * Test cost difference calculation - customer gets refund.
     */
    public function test_cost_difference_customer_gets_refund() {
        // Return product worth €50.
        $this->result->add_return_product( 1, 'SKU-001', 'Expensive Product', 50.00 );
        // New product costs €30.
        $this->result->add_new_product( 2, 'SKU-002', 'Cheaper Product', 30.00, 10 );
        // No shipping or COD (company fault).

        // Difference = 30 + 0 + 0 - 50 = -20 (refund).
        $this->assertEquals( -20.00, $this->result->get_total_cost_difference() );
    }

    /**
     * Test cost difference with equal values.
     */
    public function test_cost_difference_equal_values() {
        $this->result->add_return_product( 1, 'SKU-001', 'Product A', 25.00 );
        $this->result->add_new_product( 2, 'SKU-002', 'Product B', 25.00, 10 );

        $this->assertEquals( 0.00, $this->result->get_total_cost_difference() );
    }

    /**
     * Test adding an error.
     */
    public function test_add_error() {
        $this->result->add_error( 'Test error message' );

        $this->assertTrue( $this->result->has_errors() );
        $errors = $this->result->get_errors();
        $this->assertCount( 1, $errors );
        $this->assertEquals( 'Test error message', $errors[0] );
    }

    /**
     * Test adding multiple errors.
     */
    public function test_add_multiple_errors() {
        $this->result->add_error( 'Error 1' );
        $this->result->add_error( 'Error 2' );
        $this->result->add_error( 'Error 3' );

        $this->assertTrue( $this->result->has_errors() );
        $errors = $this->result->get_errors();
        $this->assertCount( 3, $errors );
    }

    /**
     * Test get_return_product_ids.
     */
    public function test_get_return_product_ids() {
        $this->result->add_return_product( 10, 'SKU-010', 'Product 10', 10.00 );
        $this->result->add_return_product( 20, 'SKU-020', 'Product 20', 20.00 );
        $this->result->add_return_product( 30, 'SKU-030', 'Product 30', 30.00 );

        $ids = $this->result->get_return_product_ids();

        $this->assertEquals( array( 10, 20, 30 ), $ids );
    }

    /**
     * Test get_new_product_ids.
     */
    public function test_get_new_product_ids() {
        $this->result->add_new_product( 100, 'SKU-100', 'Product 100', 100.00, 5 );
        $this->result->add_new_product( 200, 'SKU-200', 'Product 200', 200.00, 3 );

        $ids = $this->result->get_new_product_ids();

        $this->assertEquals( array( 100, 200 ), $ids );
    }

    /**
     * Test complex scenario with multiple products and fees.
     */
    public function test_complex_scenario() {
        // Return 2 products totaling €45.
        $this->result->add_return_product( 1, 'OLD-001', 'Old Item 1', 25.00 );
        $this->result->add_return_product( 2, 'OLD-002', 'Old Item 2', 20.00 );

        // Add 3 new products totaling €60.
        $this->result->add_new_product( 3, 'NEW-001', 'New Item 1', 15.00, 10 );
        $this->result->add_new_product( 4, 'NEW-002', 'New Item 2', 25.00, 5 );
        $this->result->add_new_product( 5, 'NEW-003', 'New Item 3', 20.00, 8 );

        // Add fees.
        $this->result->set_shipping_cost( 2.00 );
        $this->result->set_cod_fee( 1.00 );

        // Verify totals.
        $this->assertEquals( 45.00, $this->result->get_total_return_cost() );
        $this->assertEquals( 60.00, $this->result->get_total_new_cost() );
        $this->assertEquals( 2.00, $this->result->get_shipping_cost() );
        $this->assertEquals( 1.00, $this->result->get_cod_fee() );

        // Cost difference = 60 + 2 + 1 - 45 = 18.
        $this->assertEquals( 18.00, $this->result->get_total_cost_difference() );

        // Verify product counts.
        $this->assertCount( 2, $this->result->get_return_products() );
        $this->assertCount( 3, $this->result->get_new_products() );
        $this->assertEquals( array( 1, 2 ), $this->result->get_return_product_ids() );
        $this->assertEquals( array( 3, 4, 5 ), $this->result->get_new_product_ids() );
    }

    /**
     * Test shipping cost is cast to float.
     */
    public function test_shipping_cost_cast_to_float() {
        $this->result->set_shipping_cost( '2.50' );

        $this->assertIsFloat( $this->result->get_shipping_cost() );
        $this->assertEquals( 2.50, $this->result->get_shipping_cost() );
    }

    /**
     * Test COD fee is cast to float.
     */
    public function test_cod_fee_cast_to_float() {
        $this->result->set_cod_fee( '1.80' );

        $this->assertIsFloat( $this->result->get_cod_fee() );
        $this->assertEquals( 1.80, $this->result->get_cod_fee() );
    }
}
