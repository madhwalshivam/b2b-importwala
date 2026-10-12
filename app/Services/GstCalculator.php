<?php

namespace App\Services;

/**
 * Pure GST math. No database access, so cart, checkout, invoices and tests
 * all share the same rounding and CGST/SGST/IGST split.
 */
class GstCalculator
{
    public const PRESET_RATES = [0, 3, 5, 12, 18, 28];

    public const STATES = [
        'Andhra Pradesh', 'Arunachal Pradesh', 'Assam', 'Bihar', 'Chhattisgarh',
        'Goa', 'Gujarat', 'Haryana', 'Himachal Pradesh', 'Jharkhand',
        'Karnataka', 'Kerala', 'Madhya Pradesh', 'Maharashtra', 'Manipur',
        'Meghalaya', 'Mizoram', 'Nagaland', 'Odisha', 'Punjab',
        'Rajasthan', 'Sikkim', 'Tamil Nadu', 'Telangana', 'Tripura',
        'Uttar Pradesh', 'Uttarakhand', 'West Bengal',
        'Andaman and Nicobar Islands', 'Chandigarh',
        'Dadra and Nagar Haveli and Daman and Diu', 'Delhi',
        'Jammu and Kashmir', 'Ladakh', 'Lakshadweep', 'Puducherry',
    ];

    public static function roundMoney(float $amount): float
    {
        return round($amount, 2);
    }

    public static function normalizeState(?string $state): string
    {
        $state = trim(preg_replace('/\s+/', ' ', (string) $state) ?? '');
        if ($state === '') {
            return '';
        }
        $key = strtolower($state);
        foreach (self::STATES as $name) {
            if (strtolower($name) === $key) {
                return $name;
            }
        }
        return $state;
    }

    public static function statesMatch(?string $a, ?string $b): bool
    {
        $a = strtolower(self::normalizeState($a));
        $b = strtolower(self::normalizeState($b));
        return $a !== '' && $a === $b;
    }

    /**
     * Empty is allowed (inherit / not set). Otherwise 4, 6 or 8 digits.
     */
    public static function parseHsn(?string $raw): ?string
    {
        $raw = preg_replace('/\D+/', '', (string) $raw) ?? '';
        if ($raw === '') {
            return null;
        }
        if (!preg_match('/^\d{4}$|^\d{6}$|^\d{8}$/', $raw)) {
            throw new \InvalidArgumentException('HSN code must be 4, 6 or 8 digits.');
        }
        return $raw;
    }

    /**
     * @return float|null Null means "inherit / not set".
     */
    public static function parseRate(mixed $preset, mixed $custom = null): ?float
    {
        $preset = trim((string) $preset);
        if ($preset === '' || $preset === 'inherit') {
            return null;
        }
        if ($preset === 'custom') {
            $custom = trim((string) $custom);
            if ($custom === '') {
                return null;
            }
            if (!is_numeric($custom)) {
                throw new \InvalidArgumentException('Custom GST rate must be a number.');
            }
            $rate = (float) $custom;
        } else {
            if (!is_numeric($preset)) {
                throw new \InvalidArgumentException('GST rate must be a number.');
            }
            $rate = (float) $preset;
        }
        if ($rate < 0 || $rate > 100) {
            throw new \InvalidArgumentException('GST rate must be between 0 and 100.');
        }
        return self::roundMoney($rate);
    }

    public static function parseGstin(?string $raw): ?string
    {
        $raw = strtoupper(preg_replace('/\s+/', '', (string) $raw) ?? '');
        if ($raw === '') {
            return null;
        }
        if (!preg_match('/^[0-9]{2}[A-Z]{5}[0-9]{4}[A-Z][1-9A-Z]Z[0-9A-Z]$/', $raw)) {
            throw new \InvalidArgumentException('GSTIN must be a valid 15-character GSTIN.');
        }
        return $raw;
    }

    public static function formatRate(float $rate): string
    {
        $text = number_format($rate, 2, '.', '');
        $text = rtrim(rtrim($text, '0'), '.');
        return $text === '' ? '0' : $text;
    }

    /**
     * One cart line. Exclusive: taxable = unit * qty. Inclusive: taxable is backed out of that gross.
     *
     * @return array{taxable_amount: float, gst_amount: float, gross_amount: float, rate: float}
     */
    public static function line(float $unitPrice, int $qty, float $rate, string $priceMode): array
    {
        $qty = max(0, $qty);
        $rate = self::roundMoney($rate);
        $grossInput = self::roundMoney($unitPrice * $qty);

        if ($priceMode === 'inclusive') {
            if ($rate <= 0) {
                $taxable = $grossInput;
                $gst = 0.0;
            } else {
                $taxable = self::roundMoney($grossInput / (1 + ($rate / 100)));
                $gst = self::roundMoney($grossInput - $taxable);
            }
            $gross = $grossInput;
        } else {
            $taxable = $grossInput;
            $gst = self::roundMoney($taxable * $rate / 100);
            $gross = self::roundMoney($taxable + $gst);
        }

        return [
            'taxable_amount' => $taxable,
            'gst_amount' => $gst,
            'gross_amount' => $gross,
            'rate' => $rate,
        ];
    }

    /**
     * Split an already-rounded line GST so CGST + SGST always equals that GST.
     *
     * @return array{cgst_amount: float, sgst_amount: float, igst_amount: float, supply: string}
     */
    public static function split(float $gstAmount, ?string $shippingState, ?string $sellerState): array
    {
        $gstAmount = self::roundMoney($gstAmount);
        $seller = self::normalizeState($sellerState);
        $ship = self::normalizeState($shippingState);

        if ($ship === '') {
            return [
                'cgst_amount' => 0.0,
                'sgst_amount' => 0.0,
                'igst_amount' => 0.0,
                'supply' => 'estimated',
            ];
        }

        if ($seller === '' || !self::statesMatch($ship, $seller)) {
            return [
                'cgst_amount' => 0.0,
                'sgst_amount' => 0.0,
                'igst_amount' => $gstAmount,
                'supply' => 'inter',
            ];
        }

        $cgst = self::roundMoney($gstAmount / 2);
        $sgst = self::roundMoney($gstAmount - $cgst);
        return [
            'cgst_amount' => $cgst,
            'sgst_amount' => $sgst,
            'igst_amount' => 0.0,
            'supply' => 'intra',
        ];
    }

    /**
     * @param array<int, array{unit_price: float, quantity: int, rate: float, hsn_code?: string, product_id?: int, name?: string, sku?: string}> $lines
     */
    public static function summarize(array $lines, string $priceMode, ?string $shippingState, ?string $sellerState): array
    {
        $priceMode = $priceMode === 'inclusive' ? 'inclusive' : 'exclusive';
        $sellerMissing = self::normalizeState($sellerState) === '';
        $ship = self::normalizeState($shippingState);
        if ($ship !== '' && $sellerMissing) {
            error_log('GST: seller state is not configured; treating this order as inter-state (IGST).');
        }

        $computed = [];
        $itemsTotal = 0.0;
        $taxableTotal = 0.0;
        $gstTotal = 0.0;
        $cgstTotal = 0.0;
        $sgstTotal = 0.0;
        $igstTotal = 0.0;
        $groups = [];

        foreach ($lines as $line) {
            $rate = self::roundMoney((float) ($line['rate'] ?? 0));
            $math = self::line((float) ($line['unit_price'] ?? 0), (int) ($line['quantity'] ?? 0), $rate, $priceMode);
            $parts = self::split($math['gst_amount'], $ship, $sellerState);
            $hsn = trim((string) ($line['hsn_code'] ?? ''));

            $row = array_merge($line, $math, $parts, [
                'hsn_code' => $hsn,
                'gst_rate' => $rate,
                'total_gst_amount' => $math['gst_amount'],
            ]);
            $computed[] = $row;

            $displayBase = $priceMode === 'inclusive' ? $math['gross_amount'] : $math['taxable_amount'];
            $itemsTotal = self::roundMoney($itemsTotal + $displayBase);
            $taxableTotal = self::roundMoney($taxableTotal + $math['taxable_amount']);
            $gstTotal = self::roundMoney($gstTotal + $math['gst_amount']);
            $cgstTotal = self::roundMoney($cgstTotal + $parts['cgst_amount']);
            $sgstTotal = self::roundMoney($sgstTotal + $parts['sgst_amount']);
            $igstTotal = self::roundMoney($igstTotal + $parts['igst_amount']);

            $key = number_format($rate, 2, '.', '');
            if (!isset($groups[$key])) {
                $groups[$key] = [
                    'rate' => $rate,
                    'gst' => 0.0,
                    'cgst' => 0.0,
                    'sgst' => 0.0,
                    'igst' => 0.0,
                ];
            }
            $groups[$key]['gst'] = self::roundMoney($groups[$key]['gst'] + $math['gst_amount']);
            $groups[$key]['cgst'] = self::roundMoney($groups[$key]['cgst'] + $parts['cgst_amount']);
            $groups[$key]['sgst'] = self::roundMoney($groups[$key]['sgst'] + $parts['sgst_amount']);
            $groups[$key]['igst'] = self::roundMoney($groups[$key]['igst'] + $parts['igst_amount']);
        }

        $supply = 'estimated';
        if ($ship !== '') {
            $supply = ($sellerMissing || !self::statesMatch($ship, $sellerState)) ? 'inter' : 'intra';
        }

        $grand = $priceMode === 'inclusive'
            ? $itemsTotal
            : self::roundMoney($itemsTotal + $gstTotal);

        return [
            'lines' => $computed,
            'price_mode' => $priceMode,
            'supply' => $supply,
            'seller_state_missing' => $sellerMissing,
            'items_total' => $itemsTotal,
            'total_taxable_amount' => $taxableTotal,
            'total_gst' => $gstTotal,
            'total_cgst' => $cgstTotal,
            'total_sgst' => $sgstTotal,
            'total_igst' => $igstTotal,
            'grand_total' => $grand,
            'groups' => array_values($groups),
            'rows' => self::displayRows(array_values($groups), $supply),
        ];
    }

    /**
     * @param array<int, array{rate: float, gst: float, cgst: float, sgst: float, igst: float}> $groups
     * @return array<int, array{label: string, amount: float}>
     */
    public static function displayRows(array $groups, string $supply): array
    {
        $rows = [];
        foreach ($groups as $group) {
            $rate = (float) $group['rate'];
            $rateLabel = self::formatRate($rate);
            if ($supply === 'intra') {
                $half = self::formatRate($rate / 2);
                $rows[] = ['label' => 'CGST @' . $half . '%', 'amount' => (float) $group['cgst']];
                $rows[] = ['label' => 'SGST @' . $half . '%', 'amount' => (float) $group['sgst']];
            } elseif ($supply === 'inter') {
                $rows[] = ['label' => 'IGST @' . $rateLabel . '%', 'amount' => (float) $group['igst']];
            } else {
                $rows[] = ['label' => 'GST @' . $rateLabel . '%', 'amount' => (float) $group['gst']];
            }
        }
        return $rows;
    }
}
