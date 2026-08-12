<?php

namespace App\Services;

class TieredPricingService extends BaseService
{
    /**
     * Calculate effective unit price and subtotal for a given quantity based on tiered discounts
     */
    public function calculateUnitPrice(array $product, ?array $variation, int $quantity): array
    {
        $basePrice = (float)($product['base_price'] ?? 0.0);
        
        if ($variation && isset($variation['price_offset'])) {
            $basePrice += (float)$variation['price_offset'];
        }

        $moq = (int)($product['moq'] ?? 1);
        $effectiveQty = max($quantity, $moq);

        $unitPrice = $basePrice;
        $appliedTier = null;

        if (!empty($product['tiered_prices'])) {
            foreach ($product['tiered_prices'] as $tier) {
                $minQty = (int)$tier['min_qty'];
                $maxQty = $tier['max_qty'] !== null ? (int)$tier['max_qty'] : null;

                if ($effectiveQty >= $minQty && ($maxQty === null || $effectiveQty <= $maxQty)) {
                    $unitPrice = (float)$tier['unit_price'];
                    $appliedTier = $tier;
                    break;
                }
            }
        }

        $lineTotal = round($unitPrice * $quantity, 4);
        $savings = round(($basePrice - $unitPrice) * $quantity, 4);

        return [
            'base_unit_price' => $basePrice,
            'effective_unit_price' => $unitPrice,
            'quantity' => $quantity,
            'moq' => $moq,
            'line_total' => $lineTotal,
            'savings' => max(0.0, $savings),
            'applied_tier' => $appliedTier,
        ];
    }
}
