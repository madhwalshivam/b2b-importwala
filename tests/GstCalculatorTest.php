<?php

/**
 * Run: php tests/GstCalculatorTest.php
 */
require dirname(__DIR__) . '/app/Services/GstCalculator.php';

use App\Services\GstCalculator;

$failed = 0;

function check(string $name, bool $ok, string $detail = ''): void
{
    global $failed;
    if ($ok) {
        echo "PASS  {$name}\n";
        return;
    }
    $failed++;
    echo "FAIL  {$name}" . ($detail !== '' ? " — {$detail}" : '') . "\n";
}

function near(float $a, float $b): bool
{
    return abs($a - $b) < 0.001;
}

// Intra-state: Delhi to Delhi, exclusive, 18% on 100 x 2.
$intra = GstCalculator::summarize([
    ['unit_price' => 100, 'quantity' => 2, 'rate' => 18, 'hsn_code' => '87149990'],
], 'exclusive', 'delhi', 'Delhi');
check('intra supply', $intra['supply'] === 'intra');
check('intra taxable', near($intra['lines'][0]['taxable_amount'], 200));
check('intra gst', near($intra['lines'][0]['gst_amount'], 36));
check('intra cgst', near($intra['lines'][0]['cgst_amount'], 18));
check('intra sgst', near($intra['lines'][0]['sgst_amount'], 18));
check('intra igst zero', near($intra['lines'][0]['igst_amount'], 0));
check('intra grand', near($intra['grand_total'], 236));
check('intra split sums to gst', near(
    $intra['lines'][0]['cgst_amount'] + $intra['lines'][0]['sgst_amount'],
    $intra['lines'][0]['gst_amount']
));

// Inter-state.
$inter = GstCalculator::summarize([
    ['unit_price' => 100, 'quantity' => 1, 'rate' => 5, 'hsn_code' => '1234'],
], 'exclusive', 'Maharashtra', 'Delhi');
check('inter supply', $inter['supply'] === 'inter');
check('inter igst', near($inter['lines'][0]['igst_amount'], 5));
check('inter cgst zero', near($inter['lines'][0]['cgst_amount'], 0));
check('inter grand', near($inter['grand_total'], 105));

// Mixed rates. Totals are the sum of rounded lines, not a rate on the cart total.
$mixed = GstCalculator::summarize([
    ['unit_price' => 200, 'quantity' => 1, 'rate' => 5, 'hsn_code' => '1111'],
    ['unit_price' => 100, 'quantity' => 1, 'rate' => 18, 'hsn_code' => '2222'],
], 'exclusive', 'Karnataka', 'Delhi');
check('mixed gst', near($mixed['total_gst'], 28));
check('mixed taxable', near($mixed['total_taxable_amount'], 300));
check('mixed grand', near($mixed['grand_total'], 328));
check('mixed groups', count($mixed['groups']) === 2);
check('mixed rows are IGST', $mixed['rows'][0]['label'] === 'IGST @5%' && $mixed['rows'][1]['label'] === 'IGST @18%');

// Inclusive mode backs GST out of the shelf price and does not add it again.
$incl = GstCalculator::summarize([
    ['unit_price' => 118, 'quantity' => 1, 'rate' => 18, 'hsn_code' => '3333'],
], 'inclusive', 'Delhi', 'Delhi');
check('inclusive taxable', near($incl['lines'][0]['taxable_amount'], 100));
check('inclusive gst', near($incl['lines'][0]['gst_amount'], 18));
check('inclusive grand stays shelf price', near($incl['grand_total'], 118));
check('inclusive intra split', near($incl['total_cgst'], 9) && near($incl['total_sgst'], 9));

// 0% exempt still keeps HSN and prints a zero GST row.
$zero = GstCalculator::summarize([
    ['unit_price' => 50, 'quantity' => 3, 'rate' => 0, 'hsn_code' => '0101'],
], 'exclusive', null, 'Delhi');
check('zero gst', near($zero['total_gst'], 0));
check('zero grand', near($zero['grand_total'], 150));
check('zero hsn kept', $zero['lines'][0]['hsn_code'] === '0101');
check('zero estimated label', $zero['rows'][0]['label'] === 'GST @0%' && near($zero['rows'][0]['amount'], 0));
check('estimated before address', $zero['supply'] === 'estimated');

// Odd paise: CGST + SGST equals the rounded line GST.
$odd = GstCalculator::line(10.01, 1, 18, 'exclusive');
$split = GstCalculator::split($odd['gst_amount'], 'Delhi', 'Delhi');
check('odd paise conserved', near($split['cgst_amount'] + $split['sgst_amount'], $odd['gst_amount']));

// Missing seller state falls back to IGST once a shipping state exists.
$missing = GstCalculator::summarize([
    ['unit_price' => 100, 'quantity' => 1, 'rate' => 18, 'hsn_code' => '4444'],
], 'exclusive', 'Delhi', '');
check('missing seller is IGST', $missing['supply'] === 'inter' && near($missing['total_igst'], 18) && $missing['seller_state_missing']);

// HSN and GSTIN validation.
$hsnOk = true;
try { GstCalculator::parseHsn('12345'); $hsnOk = false; } catch (\InvalidArgumentException $e) { $hsnOk = true; }
check('hsn rejects 5 digits', $hsnOk);
check('hsn accepts 8 digits', GstCalculator::parseHsn('87149990') === '87149990');
check('hsn empty inherits', GstCalculator::parseHsn('  ') === null);
$gstinOk = GstCalculator::parseGstin('07FLOPR6641L1Z8') === '07FLOPR6641L1Z8';
check('gstin accepts sample', $gstinOk);
$badGstin = false;
try { GstCalculator::parseGstin('NOT-A-GSTIN'); } catch (\InvalidArgumentException $e) { $badGstin = true; }
check('gstin rejects junk', $badGstin);

// State normalisation.
check('state case', GstCalculator::statesMatch('  delhi ', 'Delhi'));

echo $failed === 0 ? "\nAll GST calculator checks passed.\n" : "\n{$failed} check(s) failed.\n";
exit($failed === 0 ? 0 : 1);
