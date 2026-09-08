<?php

namespace Tests\Unit;

use App\Services\PricingCalculatorService;
use PHPUnit\Framework\TestCase;

class PricingCalculatorTest extends TestCase
{
    public function test_pricing_calculator_computes_itemized_breakdown_and_total(): void
    {
        $calculator = new PricingCalculatorService();

        $pricingInput = [
            'rate_per_sqft' => 200.00,
            'base_price' => 200000.00, // 1000 sqft * 200
            'floor_rise_rate' => 1000.00, // floor rise 5th floor (4 floors * 1000)
            'facing_premium' => 5000.00,
            'plc_amount' => 10000.00,
            'parking_charges' => 15000.00,
            'clubhouse_charges' => 5000.00,
            'infrastructure_charges' => 5000.00,
            'maintenance_deposit' => 2000.00,
            'legal_charges' => 1000.00,
            'other_charges' => 1000.00,
            'gst_percent' => 5.00,
            'discount_amount' => 4000.00,
        ];

        $result = $calculator->calculate($pricingInput, 1000.00, 5);

        // Subtotal = 200000 + (4 * 1000) + 5000 + 10000 + 15000 + 5000 + 5000 + 2000 + 1000 + 1000 = 248,000
        $this->assertEquals(248000.00, $result['subtotal']);

        // Tax = 5% of 248,000 = 12,400
        $this->assertEquals(12400.00, $result['tax_amount']);

        // Final Total = 248,000 + 12,400 - 4,000 = 256,400
        $this->assertEquals(256400.00, $result['calculated_total_price']);
    }
}
