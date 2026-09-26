<?php

define('ROOT_PATH', 'c:/xampp/htdocs/importwala');
require_once ROOT_PATH . '/vendor/autoload.php';
require_once ROOT_PATH . '/config/app.php';
require_once ROOT_PATH . '/app/Core/Database.php';

spl_autoload_register(function ($class) {
    if (strncmp('App\\', $class, 4) === 0) {
        $file = ROOT_PATH . '/app/' . str_replace('\\', '/', substr($class, 4)) . '.php';
        if (file_exists($file)) {
            require_once $file;
        }
    }
});

use App\Services\BulkImportService;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;

echo "=== TESTING FORWARD-FILL PARSING IN BULK IMPORT ===\n\n";

$spreadsheet = new Spreadsheet();
$sheet = $spreadsheet->getActiveSheet();

// Write headers
$headers = BulkImportService::HEADERS;
foreach ($headers as $colIdx => $header) {
    $colLetter = \PhpOffice\PhpSpreadsheet\Cell\Coordinate::stringFromColumnIndex($colIdx + 1);
    $sheet->setCellValue($colLetter . '1', $header);
}

// Row data simulating Excel sheet with MERGED cells (blank values in subsequent rows)
// Product 1: "Cross-Border Tiger Eye Leather Bracelet" with 2 color blocks, multiple sizes under each color.

$rowsData = [
    // Row 2: First row of Product 1, Color 1 ("Brown Leather"), Size "S"
    [
        'Cross-Border Tiger Eye Leather Bracelet', // 0: Name
        'BRC-TEST-001',                           // 1: SKU
        'Fashion Jewellery',                       // 2: Category
        'Bracelets & Bangles',                     // 3: Subcategory
        'Hand-woven genuine leather bracelet.',   // 4: Description
        'Bracelet', 'Men', 'ImportWale OEM', 'Leather', '316L Stainless Steel', 'Silver', 'Tiger Eye', '21 cm', 'China', '120g', 'Vintage',
        '299.00', '2-35', '145.00', '36-149', '125.00', '150+', '99.00',
        'https://images.importwale.com/brc-main.jpg', '', '', '', '', '',
        'GRP-BRACELET-01',                         // 29: Group For Variant
        'Brown Leather',                           // 30: Variant Color Name
        'Small',                                   // 31: Variant Size Name
        'BRC-001-BRN-S',                           // 32: Variant SKU
        '145.00',                                  // 33: Variant Price
        '50',                                      // 34: Variant Stock
        'https://images.importwale.com/brn.jpg',   // 35: Variant Image
    ],
    // Row 3: Merged cells (Product Name, SKU, Group For, Variant Color are BLANK) - Size "M"
    [
        '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '',
        '',                                        // 29: Group For Variant (BLANK - merged)
        '',                                        // 30: Variant Color Name (BLANK - merged)
        'Medium',                                  // 31: Variant Size Name
        'BRC-001-BRN-M',                           // 32: Variant SKU
        '145.00',                                  // 33: Variant Price
        '60',                                      // 34: Variant Stock
        '',                                        // 35: Variant Image (BLANK - merged)
    ],
    // Row 4: Merged cells (Product Name, SKU, Group For, Variant Color are BLANK) - Size "L"
    [
        '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '',
        '',                                        // 29: Group For Variant (BLANK - merged)
        '',                                        // 30: Variant Color Name (BLANK - merged)
        'Large',                                   // 31: Variant Size Name
        'BRC-001-BRN-L',                           // 32: Variant SKU
        '145.00',                                  // 33: Variant Price
        '70',                                      // 34: Variant Stock
        '',                                        // 35: Variant Image (BLANK - merged)
    ],
    // Row 5: Same Product/Group, NEW Color ("Black Leather") - Size "S"
    [
        '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '',
        '',                                        // 29: Group For Variant (BLANK - merged)
        'Black Leather',                           // 30: Variant Color Name (NEW COLOR)
        'Small',                                   // 31: Variant Size Name
        'BRC-001-BLK-S',                           // 32: Variant SKU
        '150.00',                                  // 33: Variant Price
        '40',                                      // 34: Variant Stock
        'https://images.importwale.com/blk.jpg',   // 35: Variant Image
    ],
    // Row 6: Same Product/Group & Color ("Black Leather" BLANK - merged) - Size "M"
    [
        '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '',
        '',                                        // 29: Group For Variant (BLANK - merged)
        '',                                        // 30: Variant Color Name (BLANK - merged)
        'Medium',                                  // 31: Variant Size Name
        'BRC-001-BLK-M',                           // 32: Variant SKU
        '150.00',                                  // 33: Variant Price
        '45',                                      // 34: Variant Stock
        '',                                        // 35: Variant Image (BLANK - merged)
    ],
    // Row 7: NEW Product entirely ("Gold Hoop Earrings")
    [
        'Gold Hoop Earrings',                       // 0: Name
        'EAR-TEST-002',                            // 1: SKU
        'Earrings',                                // 2: Category
        'Hoop Earrings',                           // 3: Subcategory
        'Classic gold hoop earrings.',             // 4: Description
        'Earring', 'Women', 'ImportWale OEM', 'Gold', '18K Gold', 'Gold', 'Diamond', '15 mm', 'India', '20g', 'Modern',
        '499.00', '1-10', '450.00', '11-50', '400.00', '51+', '350.00',
        'https://images.importwale.com/ear-main.jpg', '', '', '', '', '',
        '',                                        // 29: Group For Variant
        'Yellow Gold',                             // 30: Variant Color Name
        'Standard',                                // 31: Variant Size Name
        'EAR-002-YG-STD',                          // 32: Variant SKU
        '450.00',                                  // 33: Variant Price
        '100',                                     // 34: Variant Stock
        'https://images.importwale.com/ear-yg.jpg',// 35: Variant Image
    ]
];

foreach ($rowsData as $rIdx => $rVals) {
    $rowNum = $rIdx + 2;
    foreach ($rVals as $cIdx => $val) {
        $colLetter = \PhpOffice\PhpSpreadsheet\Cell\Coordinate::stringFromColumnIndex($cIdx + 1);
        $sheet->setCellValue($colLetter . $rowNum, $val);
    }
}

$tmpPath = sys_get_temp_dir() . '/test_forward_fill_' . time() . '.xlsx';
$writer = new Xlsx($spreadsheet);
$writer->save($tmpPath);

echo "Saved test spreadsheet to: {$tmpPath}\n\n";

$service = new BulkImportService();
$res = $service->parseAndValidate($tmpPath);

if (!$res['success']) {
    echo "[FAIL] Parsing failed: " . ($res['error'] ?? 'Unknown error') . "\n";
    exit(1);
}

echo "Summary of Parsed File:\n";
print_r($res['summary']);

$products = $res['products'];
echo "\nParsed " . count($products) . " Product Group(s):\n\n";

$allPassed = true;

foreach ($products as $pIdx => $p) {
    echo "Product " . ($pIdx + 1) . ": SKU '{$p['product_sku']}' | Name: '{$p['name']}' | Total Variants: " . count($p['variants']) . "\n";
    foreach ($p['variants'] as $vIdx => $v) {
        echo "  - Row {$v['row_num']}: SKU '{$v['variant_sku']}' | Color: '" . ($v['color_name'] ?? 'NONE') . "' | Size: '" . ($v['size_label'] ?? 'NONE') . "' | Price: {$v['variant_price']} | Image: '{$v['variant_image']}'\n";
    }
    echo "\n";
}

// Validation Checks
if (count($products) !== 2) {
    echo "[FAIL] Expected 2 product groups, got " . count($products) . "\n";
    $allPassed = false;
}

$p1 = $products[0];
if (count($p1['variants']) !== 5) {
    echo "[FAIL] Product 1 expected 5 variants, got " . count($p1['variants']) . "\n";
    $allPassed = false;
}

// Check forward fill of color names for Product 1
$expectedColors = ['Brown Leather', 'Brown Leather', 'Brown Leather', 'Black Leather', 'Black Leather'];
foreach ($p1['variants'] as $i => $v) {
    if ($v['color_name'] !== $expectedColors[$i]) {
        echo "[FAIL] Variant at row {$v['row_num']} expected color '{$expectedColors[$i]}', got '{$v['color_name']}'\n";
        $allPassed = false;
    }
}

// Check forward fill of variant images for Product 1
$expectedImages = [
    'https://images.importwale.com/brn.jpg',
    'https://images.importwale.com/brn.jpg',
    'https://images.importwale.com/brn.jpg',
    'https://images.importwale.com/blk.jpg',
    'https://images.importwale.com/blk.jpg',
];
foreach ($p1['variants'] as $i => $v) {
    if ($v['variant_image'] !== $expectedImages[$i]) {
        echo "[FAIL] Variant at row {$v['row_num']} expected image '{$expectedImages[$i]}', got '{$v['variant_image']}'\n";
        $allPassed = false;
    }
}

@unlink($tmpPath);

if ($allPassed) {
    echo "=== SUCCESS! ALL FORWARD-FILL TESTS PASSED PERFECTLY! ===\n";
} else {
    echo "=== TEST FAILED! ===\n";
    exit(1);
}
