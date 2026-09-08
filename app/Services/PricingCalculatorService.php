<?php

namespace App\Services;

class PricingCalculatorService
{
    /**
     * Compute itemized pricing breakdown and final total price for real estate inventory unit.
     */
    public function calculate(array $data, float $areaSqFt = 0, int $floorNumber = 1): array
    {
        $ratePerSqFt = (float) ($data['rate_per_sqft'] ?? 0);
        $basePrice = (float) ($data['base_price'] ?? 0);
        if ($basePrice <= 0 && $ratePerSqFt > 0 && $areaSqFt > 0) {
            $basePrice = round($ratePerSqFt * $areaSqFt, 2);
        }

        $floorRiseRate = (float) ($data['floor_rise_rate'] ?? 0);
        $floorRiseTotal = round($floorRiseRate * max(0, $floorNumber - 1), 2);

        $facingPremium = (float) ($data['facing_premium'] ?? 0);
        $plcAmount = (float) ($data['plc_amount'] ?? 0);
        $parkingCharges = (float) ($data['parking_charges'] ?? 0);
        $clubhouseCharges = (float) ($data['clubhouse_charges'] ?? 0);
        $infraCharges = (float) ($data['infrastructure_charges'] ?? 0);
        $maintenanceDeposit = (float) ($data['maintenance_deposit'] ?? 0);
        $legalCharges = (float) ($data['legal_charges'] ?? 0);
        $otherCharges = (float) ($data['other_charges'] ?? 0);
        $discountAmount = (float) ($data['discount_amount'] ?? 0);

        $subtotal = $basePrice + $floorRiseTotal + $facingPremium + $plcAmount 
            + $parkingCharges + $clubhouseCharges + $infraCharges 
            + $maintenanceDeposit + $legalCharges + $otherCharges;

        $gstPercent = (float) ($data['gst_percent'] ?? 5.00);
        $taxAmount = round($subtotal * ($gstPercent / 100), 2);

        $calculatedTotalPrice = round(($subtotal + $taxAmount) - $discountAmount, 2);

        return [
            'rate_per_sqft' => $ratePerSqFt,
            'base_price' => $basePrice,
            'floor_rise_rate' => $floorRiseRate,
            'floor_rise_total' => $floorRiseTotal,
            'facing_premium' => $facingPremium,
            'plc_amount' => $plcAmount,
            'parking_charges' => $parkingCharges,
            'clubhouse_charges' => $clubhouseCharges,
            'infrastructure_charges' => $infraCharges,
            'maintenance_deposit' => $maintenanceDeposit,
            'legal_charges' => $legalCharges,
            'other_charges' => $otherCharges,
            'subtotal' => $subtotal,
            'gst_percent' => $gstPercent,
            'tax_amount' => $taxAmount,
            'discount_amount' => $discountAmount,
            'calculated_total_price' => max(0, $calculatedTotalPrice),
        ];
    }
}
