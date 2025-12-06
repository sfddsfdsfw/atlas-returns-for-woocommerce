<?php
/**
 * Unit tests for CostCalculator class.
 *
 * @package AtlasReturns\Tests\Unit
 */

namespace AtlasReturns\Tests\Unit;

use AtlasReturns\Tests\TestCase;
use AtlasReturns\Core\CostCalculator;

/**
 * Class CostCalculatorTest
 *
 * Tests for the CostCalculator class.
 */
class CostCalculatorTest extends TestCase {

    /**
     * Test reason constants are defined correctly.
     */
    public function test_reason_constants_are_defined() {
        $this->assertEquals( 'customer_fault', CostCalculator::REASON_CUSTOMER_FAULT );
        $this->assertEquals( 'company_fault', CostCalculator::REASON_COMPANY_FAULT );
        $this->assertEquals( 'company_fault_no_special_admin', CostCalculator::REASON_COMPANY_FAULT_NO_ADMIN );
    }

    /**
     * Test get_return_reasons returns all reasons when include_pro is true.
     */
    public function test_get_return_reasons_with_pro() {
        $reasons = CostCalculator::get_return_reasons( true );

        $this->assertIsArray( $reasons );
        $this->assertCount( 3, $reasons );
        $this->assertArrayHasKey( CostCalculator::REASON_CUSTOMER_FAULT, $reasons );
        $this->assertArrayHasKey( CostCalculator::REASON_COMPANY_FAULT, $reasons );
        $this->assertArrayHasKey( CostCalculator::REASON_COMPANY_FAULT_NO_ADMIN, $reasons );
    }

    /**
     * Test get_return_reasons returns only customer_fault when include_pro is false.
     */
    public function test_get_return_reasons_without_pro() {
        $reasons = CostCalculator::get_return_reasons( false );

        $this->assertIsArray( $reasons );
        $this->assertCount( 1, $reasons );
        $this->assertArrayHasKey( CostCalculator::REASON_CUSTOMER_FAULT, $reasons );
        $this->assertArrayNotHasKey( CostCalculator::REASON_COMPANY_FAULT, $reasons );
        $this->assertArrayNotHasKey( CostCalculator::REASON_COMPANY_FAULT_NO_ADMIN, $reasons );
    }

    /**
     * Test get_reason_descriptions returns descriptions for all reasons.
     */
    public function test_get_reason_descriptions() {
        $descriptions = CostCalculator::get_reason_descriptions();

        $this->assertIsArray( $descriptions );
        $this->assertCount( 3, $descriptions );
        $this->assertArrayHasKey( CostCalculator::REASON_CUSTOMER_FAULT, $descriptions );
        $this->assertArrayHasKey( CostCalculator::REASON_COMPANY_FAULT, $descriptions );
        $this->assertArrayHasKey( CostCalculator::REASON_COMPANY_FAULT_NO_ADMIN, $descriptions );

        // Check descriptions are not empty.
        foreach ( $descriptions as $desc ) {
            $this->assertNotEmpty( $desc );
            $this->assertIsString( $desc );
        }
    }

    /**
     * Test get_reason_descriptions returns strings.
     */
    public function test_get_reason_descriptions_are_strings() {
        $descriptions = CostCalculator::get_reason_descriptions();

        foreach ( $descriptions as $reason => $description ) {
            $this->assertIsString( $description, "Description for {$reason} should be a string" );
        }
    }

    /**
     * Test customer fault reason label.
     */
    public function test_customer_fault_reason_label() {
        $reasons = CostCalculator::get_return_reasons( true );

        $this->assertEquals( 'Customer Fault', $reasons[ CostCalculator::REASON_CUSTOMER_FAULT ] );
    }

    /**
     * Test company fault reason label.
     */
    public function test_company_fault_reason_label() {
        $reasons = CostCalculator::get_return_reasons( true );

        $this->assertEquals( 'Company Fault - Special Handling', $reasons[ CostCalculator::REASON_COMPANY_FAULT ] );
    }

    /**
     * Test company fault no admin reason label.
     */
    public function test_company_fault_no_admin_reason_label() {
        $reasons = CostCalculator::get_return_reasons( true );

        $this->assertEquals( 'Company Fault - No Special Handling', $reasons[ CostCalculator::REASON_COMPANY_FAULT_NO_ADMIN ] );
    }
}
