<?php
/**
 * Base test case for Atlas Returns tests.
 *
 * @package AtlasReturns\Tests
 */

namespace AtlasReturns\Tests;

use PHPUnit\Framework\TestCase as PHPUnitTestCase;

/**
 * Class TestCase
 *
 * Base test case with common functionality.
 */
abstract class TestCase extends PHPUnitTestCase {

    /**
     * Set up test fixtures.
     */
    protected function setUp(): void {
        parent::setUp();
    }

    /**
     * Tear down test fixtures.
     */
    protected function tearDown(): void {
        parent::tearDown();
    }

    /**
     * Create a mock WC_Order object.
     *
     * @param array $data Order data.
     * @return \PHPUnit\Framework\MockObject\MockObject
     */
    protected function createMockOrder( array $data = array() ) {
        $defaults = array(
            'id'              => 123,
            'billing_phone'   => '1234567890',
            'billing_email'   => 'test@example.com',
            'billing_address' => array(
                'first_name' => 'John',
                'last_name'  => 'Doe',
                'address_1'  => '123 Test St',
                'city'       => 'Test City',
                'postcode'   => '12345',
                'country'    => 'GR',
            ),
            'items'           => array(),
        );

        $data = array_merge( $defaults, $data );

        $order = $this->createMock( \WC_Order::class );

        $order->method( 'get_id' )->willReturn( $data['id'] );
        $order->method( 'get_billing_phone' )->willReturn( $data['billing_phone'] );
        $order->method( 'get_billing_email' )->willReturn( $data['billing_email'] );
        $order->method( 'get_billing_first_name' )->willReturn( $data['billing_address']['first_name'] );
        $order->method( 'get_billing_last_name' )->willReturn( $data['billing_address']['last_name'] );
        $order->method( 'get_billing_address_1' )->willReturn( $data['billing_address']['address_1'] );
        $order->method( 'get_billing_city' )->willReturn( $data['billing_address']['city'] );
        $order->method( 'get_billing_postcode' )->willReturn( $data['billing_address']['postcode'] );
        $order->method( 'get_billing_country' )->willReturn( $data['billing_address']['country'] );
        $order->method( 'get_items' )->willReturn( $data['items'] );

        return $order;
    }

    /**
     * Create a mock WC_Order_Item_Product object.
     *
     * @param array $data Item data.
     * @return \PHPUnit\Framework\MockObject\MockObject
     */
    protected function createMockOrderItem( array $data = array() ) {
        $defaults = array(
            'product_id' => 1,
            'name'       => 'Test Product',
            'sku'        => 'TEST-SKU',
            'quantity'   => 1,
            'total'      => 10.00,
        );

        $data = array_merge( $defaults, $data );

        $product = $this->createMock( \WC_Product::class );
        $product->method( 'get_id' )->willReturn( $data['product_id'] );
        $product->method( 'get_sku' )->willReturn( $data['sku'] );
        $product->method( 'get_price' )->willReturn( $data['total'] );
        $product->method( 'get_name' )->willReturn( $data['name'] );

        $item = $this->createMock( \WC_Order_Item_Product::class );
        $item->method( 'get_product' )->willReturn( $product );
        $item->method( 'get_product_id' )->willReturn( $data['product_id'] );
        $item->method( 'get_name' )->willReturn( $data['name'] );
        $item->method( 'get_quantity' )->willReturn( $data['quantity'] );
        $item->method( 'get_total' )->willReturn( $data['total'] );

        return $item;
    }
}
