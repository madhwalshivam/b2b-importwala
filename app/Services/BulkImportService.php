<?php

namespace App\Services;

use App\Core\Database;
use App\Models\Product;
use App\Models\ProductVariant;
use App\Models\ProductSpecification;
use App\Models\Category;
use App\Models\Subcategory;
use App\Models\Brand;
use App\Models\Factory;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use PhpOffice\PhpSpreadsheet\IOFactory;
use ZipArchive;

class BulkImportService
{
    private \PDO $db;
    private Product $productModel;
    private ProductVariant $variantModel;
    private ProductSpecification $specModel;
    private Factory $factoryModel;

    private ?array $categoryCache = null;
    private ?array $subcategoryCache = null;
    private ?array $brandCache = null;

    public const HEADERS = [
        'Product Name',                                                        // 0
        'Product SKU',                                                         // 1
        'Category',                                                            // 2
        'Subcategory',                                                         // 3
        'Description',                                                         // 4
        'Jewellery Type',                                                      // 5
        'Gender',                                                              // 6
        'Brand Name',                                                          // 7
        'Material/Metal Type',                                                 // 8
        'Metal Color',                                                         // 9
        'Main Stone Type',                                                     // 10
        'Size',                                                                // 11
        'Country of Origin',                                                   // 12  (Material col removed; was 13)
        'Weight',                                                              // 13 (was 14)
        'Variety',                                                             // 14 (was 15)
        'One Piece Price',                                                     // 15
        'Wholesale Tier 1 Qty Range',                                          // 16
        'Wholesale Tier 1 Price',                                              // 17
        'Wholesale Tier 2 Qty Range',                                          // 18
        'Wholesale Tier 2 Price',                                              // 19
        'Wholesale Tier 3 Qty Range',                                          // 20
        'Wholesale Tier 3 Price',                                              // 21
        'Main Product Image',                                                  // 22
        'Additional Image 1',                                                  // 23
        'Additional Image 2',                                                  // 24
        'Additional Image 3',                                                  // 25
        'Additional Image 4',                                                  // 26
        'Product Video URL',                                                   // 27
        'Variation Type',                                                      // 28
        'Variation Value',                                                     // 29
        'Variation SKU',                                                       // 30
        'Variation Price',                                                     // 31
        'Variation Stock',                                                     // 32
        'Variation Image',                                                     // 33
        'Processing Technology',                                               // 34  (split 1/3; was combined col 35)
        'Processing Technique',                                                // 35  (split 2/3; NEW)
        'Treatment Process',                                                   // 36  (split 3/3; NEW)
        'Style',                                                               // 37
        'Suitable For Gift Giving Occasion',                                   // 38
        'Item Number',                                                         // 39
        'Main Downstream Platform',                                            // 40
        'Color',                                                               // 41
        'Popular Elements',                                                    // 42
        'Style Classification',                                                // 43
        'Kind',                                                                // 44  (split 1/3; was combined col 43)
        'Product Type',                                                        // 45  (split 2/3; NEW)
        'Chain Style',                                                         // 46  (Jewellery Type col removed; was 47)
        'Pendant Material',                                                    // 47
        'Trendy Element',                                                      // 48
        'Closure Type',                                                        // 49
        'Manufacturer ID',                                                     // 50 (PRIVATE 1)
        'Manufacturer Name',                                                   // 51 (PRIVATE 2)
        'Manufacturer Contact Person',                                         // 52 (PRIVATE 3)
        'Manufacturer Phone',                                                  // 53 (PRIVATE 4)
        'Manufacturer WhatsApp',                                               // 54 (PRIVATE 5)
        'Manufacturer Email',                                                  // 55 (PRIVATE 6)
        'Manufacturer Store URL',                                              // 56 (PRIVATE 7)
        'Source Platform',                                                     // 57 (PRIVATE 8)
        'Source Product ID',                                                   // 58 (PRIVATE 9)
        'Source Product URL',                                                  // 59 (PRIVATE 10)
        'Import Date',                                                         // 60 (PRIVATE 11)
        'Status',                                                              // 61 (PRIVATE 12)
    ];

    public function __construct()
    {
        $this->db = Database::getInstance();
        $this->productModel = new Product();
        $this->variantModel = new ProductVariant();
        $this->specModel = new ProductSpecification();
        $this->factoryModel = new Factory();
    }

    /**
     * Generate pre-filled sample spreadsheet template (XLSX or CSV).
     */
    public function generateTemplate(string $format = 'xlsx'): string
    {
        $spreadsheet = new Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();
        $sheet->setTitle('Bulk Import Template');

        // Header Row
        foreach (self::HEADERS as $colIdx => $header) {
            $colLetter = \PhpOffice\PhpSpreadsheet\Cell\Coordinate::stringFromColumnIndex($colIdx + 1);
            $sheet->setCellValue($colLetter . '1', $header);
            $sheet->getStyle($colLetter . '1')->getFont()->setBold(true);

            // Give Private fields a distinct dark slate header, Public fields orange header
            if ($colIdx >= 50) {
                $sheet->getStyle($colLetter . '1')->getFill()->setFillType(\PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID)
                    ->getStartColor()->setARGB('FF334155'); // Dark slate for internal/private fields
            } else {
                $sheet->getStyle($colLetter . '1')->getFill()->setFillType(\PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID)
                    ->getStartColor()->setARGB('FFF05A29'); // Orange for public fields
            }
            $sheet->getStyle($colLetter . '1')->getFont()->getColor()->setARGB('FFFFFFFF');
        }

        // Sample Data Row 1 (Product with 1st Variant) — 62-column layout
        $sampleRow1 = [
            'Cross-Border Tiger Eye Leather Bracelet',                        // 0:  Product Name
            'JWL-BRC-001',                                                    // 1:  Product SKU
            'Fashion Jewellery',                                              // 2:  Category
            'Bracelets & Bangles',                                            // 3:  Subcategory
            'Hand-woven genuine leather bracelet with natural Tiger Eye stone.', // 4: Description
            'Bracelet',                                                       // 5:  Jewellery Type
            'Men',                                                            // 6:  Gender
            'ImportWale OEM',                                                 // 7:  Brand Name
            '316L Stainless Steel & Leather',                                 // 8:  Material/Metal Type
            'Silver / Black PVD',                                             // 9:  Metal Color
            'Natural Tiger Eye Stone',                                        // 10: Main Stone Type
            '21 cm',                                                          // 11: Size
            'China',                                                          // 12: Country of Origin
            '120g',                                                           // 13: Weight
            'Vintage Braided',                                                // 14: Variety
            '299.00',                                                         // 15: One Piece Price
            '2-35',                                                           // 16: Wholesale Tier 1 Qty Range
            '145.00',                                                         // 17: Wholesale Tier 1 Price
            '36-149',                                                         // 18: Wholesale Tier 2 Qty Range
            '125.00',                                                         // 19: Wholesale Tier 2 Price
            '150+',                                                           // 20: Wholesale Tier 3 Qty Range
            '99.00',                                                          // 21: Wholesale Tier 3 Price
            'https://images.importwale.com/products/jwl-brc-001-main.jpg',    // 22: Main Product Image
            'https://images.importwale.com/products/jwl-brc-001-1.jpg',       // 23: Additional Image 1
            'https://images.importwale.com/products/jwl-brc-001-2.jpg',       // 24: Additional Image 2
            'https://images.importwale.com/products/jwl-brc-001-3.jpg',       // 25: Additional Image 3
            'https://images.importwale.com/products/jwl-brc-001-4.jpg',       // 26: Additional Image 4
            'https://media.importwale.com/videos/jwl-brc-001.mp4',            // 27: Product Video URL
            'Color',                                                          // 28: Variation Type
            'Brown Leather - Gold Clasp',                                     // 29: Variation Value
            'JWL-BRC-001-BRN-GLD',                                            // 30: Variation SKU
            '145.00',                                                         // 31: Variation Price
            '500',                                                            // 32: Variation Stock
            'https://images.importwale.com/products/jwl-brc-001-brn-gld.jpg', // 33: Variation Image
            'Vacuum Electroplating',                                          // 34: Processing Technology (split 1/3)
            'Hand Weaving',                                                   // 35: Processing Technique (split 2/3)
            '',                                                               // 36: Treatment Process (split 3/3)
            'Vintage / Punk',                                                 // 37: Style
            "Birthday, Father's Day",                                         // 38: Gift Occasion
            'JWL-2026-BRC01',                                                 // 39: Item Number
            'Amazon, Flipkart, Meesho',                                       // 40: Main Downstream Platform
            'Brown / Tiger Eye',                                              // 41: Color
            'Geometry, Leather Weave',                                        // 42: Popular Elements
            'Fashion Commuter',                                               // 43: Style Classification
            "Men's",                                                          // 44: Kind (split 1/3)
            'Leather Bracelet',                                               // 45: Product Type (split 2/3)
            'Braided Rope Chain',                                             // 46: Chain Style (Jewellery Type col removed)
            'N/A',                                                            // 47: Pendant Material
            'Retro Braided Leather',                                          // 48: Trendy Element
            'Magnetic Clasp',                                                 // 49: Closure Type
            'FCT-001',                                                        // 50: Manufacturer ID (Private)
            'Yiwu Fashion Jewelry Manufactory',                               // 51: Manufacturer Name (Private)
            'Mr. Chen',                                                       // 52: Manufacturer Contact Person (Private)
            '+86 138 0000 1111',                                              // 53: Phone (Private)
            '+86 138 0000 1111',                                              // 54: WhatsApp (Private)
            'chen@yiwujewelry.cn',                                            // 55: Email (Private)
            'https://shop12345.1688.com',                                     // 56: Store URL (Private)
            '1688',                                                           // 57: Source Platform (Private)
            '685412985412',                                                   // 58: Source Product ID (Private)
            'https://detail.1688.com/offer/685412985412.html',                // 59: Source Product URL (Private)
            date('Y-m-d H:i:s'),                                              // 60: Import Date (Private)
            'Active',                                                         // 61: Status (Private)
        ];

        foreach ($sampleRow1 as $cIdx => $val) {
            $colLetter = \PhpOffice\PhpSpreadsheet\Cell\Coordinate::stringFromColumnIndex($cIdx + 1);
            $sheet->setCellValue($colLetter . '2', $val);
        }

        // Auto-fit column widths
        foreach (range(1, count(self::HEADERS)) as $colIdx) {
            $colLetter = \PhpOffice\PhpSpreadsheet\Cell\Coordinate::stringFromColumnIndex($colIdx);
            $sheet->getColumnDimension($colLetter)->setAutoSize(true);
        }

        $tmpPath = sys_get_temp_dir() . '/importwala_template_' . time() . '.' . $format;
        if (strtolower($format) === 'csv') {
            $writer = IOFactory::createWriter($spreadsheet, 'Csv');
        } else {
            $writer = new Xlsx($spreadsheet);
        }
        $writer->save($tmpPath);

        return $tmpPath;
    }

    /**
     * Parse spreadsheet file & validate contents with Factory Auto-linking.
     */
    public function parseAndValidate(string $filePath, ?string $zipFilePath = null, bool $autoCreateCategory = true): array
    {
        $extractedZipDir = null;
        if ($zipFilePath && file_exists($zipFilePath)) {
            $extractedZipDir = $this->extractZipImages($zipFilePath);
        }

        // Load Spreadsheet
        try {
            $spreadsheet = IOFactory::load($filePath);
            $sheet = $spreadsheet->getActiveSheet();
            $rows = $sheet->toArray(null, true, true, true);
        } catch (\Throwable $e) {
            return [
                'success' => false,
                'error'   => 'Unable to parse spreadsheet file: ' . $e->getMessage()
            ];
        }

        if (count($rows) <= 1) {
            return [
                'success' => false,
                'error'   => 'The uploaded file is empty or missing data rows.'
            ];
        }

        // Skip header
        $headerRow = array_values(array_shift($rows));
        
        $parsedRows = [];
        $fileVariantSkus = [];
        $rowIndex = 2; // 1-indexed Excel row counter

        // Cache category, brand, and existing factory lookups
        $categoryCache = $this->getCategoryCache();
        $subcategoryCache = $this->getSubcategoryCache();
        $brandCache = $this->getBrandCache();

        // Build Factory Cache by code
        $dbFactories = $this->factoryModel->all();
        $factoryCache = [];
        foreach ($dbFactories as $f) {
            $factoryCache[strtoupper($f['factory_code'])] = $f;
        }

        $stagedNewFactories = [];
        $currentProductGroup = null;
        $groupedProducts = [];

        foreach ($rows as $rowMap) {
            $row = array_values($rowMap);
            // Check empty row
            if (empty(array_filter($row, fn($val) => trim((string)$val) !== ''))) {
                $rowIndex++;
                continue;
            }

            // Extract fields based on 60-column schema
            $productName   = trim((string)($row[0] ?? ''));
            $productSku    = strtoupper(trim((string)($row[1] ?? '')));

            // Forward-fill product level fields if productSku is empty but previous product group exists
            if (empty($productSku) && $currentProductGroup !== null) {
                $productSku = $currentProductGroup;
            }

            if (!empty($productSku)) {
                $currentProductGroup = $productSku;
            }

            if (empty($productSku)) {
                $parsedRows[] = [
                    'row_num' => $rowIndex,
                    'status'  => 'error',
                    'error'   => "Row {$rowIndex}: Missing required 'Product SKU'.",
                    'data'    => $row
                ];
                $rowIndex++;
                continue;
            }

            // Group additional images from columns 23..26 (shifted -1 after Material col removal)
            $addImgs = array_filter([
                trim((string)($row[23] ?? '')),
                trim((string)($row[24] ?? '')),
                trim((string)($row[25] ?? '')),
                trim((string)($row[26] ?? '')),
            ]);

            // Manufacturer Auto-Linking Logic (Section 2) — private cols shifted to 51+
            $mfgIdCode = strtoupper(trim((string)($row[51] ?? '')));
            $mfgName   = trim((string)($row[52] ?? ''));

            $factoryLinkStatus = 'unassigned'; // 'existing', 'new', 'unassigned'
            $factoryCode       = null;
            $factoryName       = null;
            $factoryBadge      = 'Unassigned';
            $factoryId         = null;

            if (!empty($mfgIdCode)) {
                // Rule a): Check if Manufacturer ID matches an existing Factory in DB
                if (isset($factoryCache[$mfgIdCode])) {
                    $factoryId         = (int)$factoryCache[$mfgIdCode]['id'];
                    $factoryCode       = $factoryCache[$mfgIdCode]['factory_code'];
                    $factoryName       = $factoryCache[$mfgIdCode]['name'];
                    $factoryLinkStatus = 'existing';
                    $factoryBadge      = "Existing [{$factoryCode}] {$factoryName}";
                }
                // Rule d): Staged new factory from earlier row in same sheet
                elseif (isset($stagedNewFactories[$mfgIdCode])) {
                    $factoryCode       = $stagedNewFactories[$mfgIdCode]['factory_code'];
                    $factoryName       = $stagedNewFactories[$mfgIdCode]['name'];
                    $factoryLinkStatus = 'new';
                    $factoryBadge      = "New [{$factoryCode}] {$factoryName}";
                }
                // Rule b): Auto-create new Factory record
                else {
                    $targetCode = str_starts_with($mfgIdCode, 'FCT-') ? $mfgIdCode : $this->factoryModel->generateNextCode();
                    $targetName = !empty($mfgName) ? $mfgName : ('Factory ' . $targetCode);

                    $stagedNewFactoryData = [
                        'factory_code'    => $targetCode,
                        'name'            => $targetName,
                        'contact_person'  => trim((string)($row[53] ?? '')) ?: null,
                        'phone'           => trim((string)($row[54] ?? '')) ?: null,
                        'whatsapp'        => trim((string)($row[55] ?? '')) ?: null,
                        'email'           => trim((string)($row[56] ?? '')) ?: null,
                        'store_url'       => trim((string)($row[57] ?? '')) ?: null,
                        'source_platform' => trim((string)($row[58] ?? '')) ?: null,
                        'status'          => 'active',
                        'notes'           => 'Auto-created via Bulk Product Sheet Importer.',
                    ];

                    $stagedNewFactories[$mfgIdCode] = $stagedNewFactoryData;
                    if ($targetCode !== $mfgIdCode) {
                        $stagedNewFactories[$targetCode] = $stagedNewFactoryData;
                    }

                    $factoryCode       = $targetCode;
                    $factoryName       = $targetName;
                    $factoryLinkStatus = 'new';
                    $factoryBadge      = "New [{$factoryCode}] {$factoryName}";
                }
            }

            if (!isset($groupedProducts[$productSku])) {
                $groupedProducts[$productSku] = [
                    'product_sku'             => $productSku,
                    'name'                    => $productName,
                    'category'                => trim((string)($row[2] ?? '')),
                    'subcategory'             => trim((string)($row[3] ?? '')),
                    'description'             => trim((string)($row[4] ?? '')),
                    'jewellery_type'          => trim((string)($row[5] ?? '')),
                    'gender'                  => trim((string)($row[6] ?? '')),
                    'brand'                   => trim((string)($row[7] ?? '')),
                    'material_type'           => trim((string)($row[8] ?? '')),  // Material/Metal Type (consolidated)
                    'metal_color'             => trim((string)($row[9] ?? '')),
                    'stone_type'              => trim((string)($row[10] ?? '')),
                    'size'                    => trim((string)($row[11] ?? '')),
                    // col 12 = Country of Origin (Material col removed)
                    'country_of_origin'       => trim((string)($row[12] ?? '')),
                    'weight'                  => trim((string)($row[13] ?? '')),
                    'variety'                 => trim((string)($row[14] ?? '')),
                    'one_piece_price'         => (float)($row[15] ?? 0),
                    'tier1_qty'               => trim((string)($row[16] ?? '')),
                    'tier1_price'             => (float)($row[17] ?? 0),
                    'tier2_qty'               => trim((string)($row[18] ?? '')),
                    'tier2_price'             => (float)($row[19] ?? 0),
                    'tier3_qty'               => trim((string)($row[20] ?? '')),
                    'tier3_price'             => (float)($row[21] ?? 0),
                    'main_image'              => trim((string)($row[22] ?? '')),
                    'additional_images'       => implode(',', $addImgs),
                    'video_url'               => trim((string)($row[27] ?? '')),
                    // Processing Technology split into 3 cols (34, 35, 36)
                    'processing_technology'   => trim((string)($row[34] ?? '')),
                    'processing_technique'    => trim((string)($row[35] ?? '')),
                    'treatment_process'       => trim((string)($row[36] ?? '')),
                    'style'                   => trim((string)($row[37] ?? '')),
                    'gift_occasion'           => trim((string)($row[38] ?? '')),
                    'item_number'             => trim((string)($row[39] ?? '')),
                    'downstream_platform'     => trim((string)($row[40] ?? '')),
                    'color'                   => trim((string)($row[41] ?? '')),
                    'popular_elements'        => trim((string)($row[42] ?? '')),
                    'style_classification'    => trim((string)($row[43] ?? '')),
                    // Kind/Product Type split into 2 cols (44, 45) — Jewellery Type col removed
                    'kind'                    => trim((string)($row[44] ?? '')),
                    'product_type'            => trim((string)($row[45] ?? '')),
                    'chain_style'             => trim((string)($row[46] ?? '')),
                    'pendant_material'        => trim((string)($row[47] ?? '')),
                    'trendy_element'          => trim((string)($row[48] ?? '')),
                    'closure_type'            => trim((string)($row[49] ?? '')),
                    // Private Manufacturer Fields (Columns 50..61)
                    'factory_id'                  => $factoryId,
                    'factory_code'                => $factoryCode,
                    'factory_name'                => $factoryName,
                    'factory_link_status'         => $factoryLinkStatus,
                    'factory_badge'               => $factoryBadge,
                    'manufacturer_id_code'        => $mfgIdCode,
                    'manufacturer_name'           => $mfgName,
                    'manufacturer_contact_person' => trim((string)($row[52] ?? '')),
                    'manufacturer_phone'          => trim((string)($row[53] ?? '')),
                    'manufacturer_whatsapp'       => trim((string)($row[54] ?? '')),
                    'manufacturer_email'          => trim((string)($row[55] ?? '')),
                    'manufacturer_store_url'      => trim((string)($row[56] ?? '')),
                    'source_platform'             => trim((string)($row[57] ?? '')),
                    'source_product_id'           => trim((string)($row[58] ?? '')),
                    'source_product_url'          => trim((string)($row[59] ?? '')),
                    'import_date'                 => !empty(trim((string)($row[60] ?? ''))) ? trim((string)$row[60]) : date('Y-m-d H:i:s'),
                    'admin_status'                => !empty(trim((string)($row[61] ?? ''))) ? trim((string)$row[61]) : 'Active',
                    'moq'                         => 1,
                    'available_qty'               => 100,
                    'variants'                    => [],
                    'rows'                        => [],
                    'errors'                      => [],
                    'warnings'                    => [],
                ];
            } else {
                // Forward fill product level attributes if subsequent row has non-empty fields
                if (empty($groupedProducts[$productSku]['name']) && !empty($productName)) {
                    $groupedProducts[$productSku]['name'] = $productName;
                }
                if (empty($groupedProducts[$productSku]['main_image']) && !empty($row[23])) {
                    $groupedProducts[$productSku]['main_image'] = trim((string)$row[23]);
                }
            }

            $pGroup = &$groupedProducts[$productSku];
            $pGroup['rows'][] = $rowIndex;

            // Variant Level Data (Columns 28..33 — shifted -1 after Material col removal)
            $varType  = trim((string)($row[28] ?? ''));
            $varVal   = trim((string)($row[29] ?? ''));
            $varSku   = strtoupper(trim((string)($row[30] ?? '')));
            $varPrice = (float)($row[31] ?? 0);
            $varStock = (int)($row[32] ?? $pGroup['available_qty']);
            $varImg   = trim((string)($row[33] ?? ''));

            // Default variant SKU to product SKU if empty
            if (empty($varSku)) {
                $varSku = count($pGroup['variants']) === 0 ? $productSku : ($productSku . '-V' . (count($pGroup['variants']) + 1));
            }

            // Check duplicate variant SKU in file
            if (isset($fileVariantSkus[$varSku])) {
                $pGroup['errors'][] = "Row {$rowIndex}: Duplicate Variant SKU '{$varSku}' in file (first seen on row {$fileVariantSkus[$varSku]}).";
            } else {
                $fileVariantSkus[$varSku] = $rowIndex;
            }

            $pGroup['variants'][] = [
                'row_num'         => $rowIndex,
                'variation_type'  => !empty($varType) ? $varType : 'Variant',
                'variation_value' => !empty($varVal) ? $varVal : 'Default',
                'variant_sku'     => $varSku,
                'variant_price'   => $varPrice > 0 ? $varPrice : ($pGroup['tier1_price'] > 0 ? $pGroup['tier1_price'] : $pGroup['one_piece_price']),
                'one_piece_price' => $pGroup['one_piece_price'],
                'variant_stock'   => $varStock,
                'variant_image'   => $varImg,
            ];

            $rowIndex++;
        }

        // Validate Product Level Records
        $validProductsCount = 0;
        $warningProductsCount = 0;
        $errorProductsCount = 0;
        $totalVariants = 0;

        foreach ($groupedProducts as $sku => &$prod) {
            // Check required product name
            if (empty($prod['name'])) {
                $prod['errors'][] = "Product SKU '{$sku}': Missing required 'Product Name'.";
            }

            // Check category
            if (empty($prod['category'])) {
                $prod['errors'][] = "Product SKU '{$sku}': Missing required 'Category'.";
            } else {
                $catLower = strtolower($prod['category']);
                if (!isset($categoryCache[$catLower])) {
                    if ($autoCreateCategory) {
                        $prod['warnings'][] = "Category '{$prod['category']}' does not exist in DB — will be auto-created.";
                    } else {
                        $prod['errors'][] = "Category '{$prod['category']}' does not exist in DB.";
                    }
                }
            }

            // Check pricing
            if ($prod['one_piece_price'] <= 0 && $prod['tier1_price'] <= 0) {
                $prod['errors'][] = "Product SKU '{$sku}': Must provide at least 'One Piece Price' or 'Wholesale Tier 1 Price'.";
            }

            // Image handling checks
            if ($extractedZipDir && !empty($prod['main_image'])) {
                $resolvedMain = $this->resolveZipImage($prod['main_image'], $extractedZipDir);
                if (!$resolvedMain && !filter_var($prod['main_image'], FILTER_VALIDATE_URL)) {
                    $prod['warnings'][] = "Main image '{$prod['main_image']}' not found in ZIP or URL.";
                }
            }

            $totalVariants += count($prod['variants']);

            if (!empty($prod['errors'])) {
                $prod['status'] = 'error';
                $errorProductsCount++;
            } elseif (!empty($prod['warnings'])) {
                $prod['status'] = 'warning';
                $warningProductsCount++;
            } else {
                $prod['status'] = 'valid';
                $validProductsCount++;
            }
        }
        unset($prod);

        return [
            'success' => true,
            'summary' => [
                'total_rows'        => $rowIndex - 2,
                'total_products'    => count($groupedProducts),
                'total_variants'    => $totalVariants,
                'valid_products'    => $validProductsCount,
                'warning_products'  => $warningProductsCount,
                'error_products'    => $errorProductsCount,
                'staged_factories'  => count($stagedNewFactories),
            ],
            'products'         => array_values($groupedProducts),
            'staged_factories' => array_values($stagedNewFactories),
            'extracted_zip_dir'=> $extractedZipDir,
        ];
    }

    /**
     * Execute Database Commit for validated products with Factory creation & assignment.
     */
    public function commitImport(array $productsData, ?string $extractedZipDir = null): array
    {
        $createdProducts = 0;
        $updatedProducts = 0;
        $createdVariants = 0;
        $updatedVariants = 0;
        $skippedProducts = 0;
        $createdFactoriesCount = 0;
        $errorLogs = [];

        $this->db->beginTransaction();

        try {
            // Step 1: Commit any newly staged Factories into DB first
            $factoryMapByCode = [];
            $dbFactories = $this->factoryModel->all();
            foreach ($dbFactories as $f) {
                $factoryMapByCode[strtoupper($f['factory_code'])] = (int)$f['id'];
            }

            foreach ($productsData as $prod) {
                if (($prod['status'] ?? '') === 'error') continue;

                $mfgIdCode = strtoupper(trim($prod['manufacturer_id_code'] ?? ''));
                if (!empty($mfgIdCode) && empty($prod['factory_id'])) {
                    if (isset($factoryMapByCode[$mfgIdCode])) {
                        // Linked to existing
                        $factoryId = $factoryMapByCode[$mfgIdCode];
                    } else {
                        // Create factory record in DB
                        $code = !empty($prod['factory_code']) ? $prod['factory_code'] : $this->factoryModel->generateNextCode();
                        $name = !empty($prod['factory_name']) ? $prod['factory_name'] : ($prod['manufacturer_name'] ?: ('Factory ' . $code));

                        $factoryId = $this->factoryModel->insert([
                            'factory_code'    => $code,
                            'name'            => $name,
                            'contact_person'  => $prod['manufacturer_contact_person'] ?: null,
                            'phone'           => $prod['manufacturer_phone'] ?: null,
                            'whatsapp'        => $prod['manufacturer_whatsapp'] ?: null,
                            'email'           => $prod['manufacturer_email'] ?: null,
                            'store_url'       => $prod['manufacturer_store_url'] ?: null,
                            'source_platform' => $prod['source_platform'] ?: null,
                            'status'          => 'active',
                            'notes'           => 'Auto-created via Bulk Product Sheet Importer.',
                            'created_at'      => date('Y-m-d H:i:s'),
                            'updated_at'      => date('Y-m-d H:i:s'),
                        ]);

                        $factoryMapByCode[$code] = $factoryId;
                        $factoryMapByCode[$mfgIdCode] = $factoryId;
                        $createdFactoriesCount++;
                    }
                }
            }

            // Step 2: Commit Products
            foreach ($productsData as $prod) {
                if (($prod['status'] ?? '') === 'error') {
                    $skippedProducts++;
                    $errorLogs[] = [
                        'sku'    => $prod['product_sku'],
                        'name'   => $prod['name'],
                        'reason' => implode('; ', $prod['errors'] ?? ['Validation failed.'])
                    ];
                    continue;
                }

                $sku = $prod['product_sku'];
                $categoryId = $this->resolveOrCreateCategory($prod['category']);
                $subcategoryId = !empty($prod['subcategory']) ? $this->resolveOrCreateSubcategory($categoryId, $prod['subcategory']) : null;
                $brandId = !empty($prod['brand']) ? $this->resolveOrCreateBrand($prod['brand']) : null;

                // Resolve Factory ID
                $mfgIdCode = strtoupper(trim($prod['manufacturer_id_code'] ?? ''));
                $assignedFactoryId = !empty($prod['factory_id']) ? (int)$prod['factory_id'] : ($factoryMapByCode[$mfgIdCode] ?? null);

                // Process Main Image
                $mainImagePath = $this->processImageSource($prod['main_image'], $extractedZipDir);

                // Check existing product in DB
                $stmtCheck = $this->db->prepare("SELECT id FROM products WHERE sku = ?");
                $stmtCheck->execute([$sku]);
                $existing = $stmtCheck->fetch();

                $slug = $this->slugify($prod['name']) . '-' . strtolower($sku);

                if ($existing) {
                    $productId = (int)$existing['id'];
                    $stmtUpdate = $this->db->prepare("
                        UPDATE products SET
                            name = :name,
                            slug = :slug,
                            category_id = :category_id,
                            subcategory_id = :subcategory_id,
                            brand_id = :brand_id,
                            factory_id = :factory_id,
                            weight = :weight,
                            variety = :variety,
                            description = :description,
                            price = :price,
                            sale_price = :sale_price,
                            moq = :moq,
                            stock = :stock,
                            main_image = COALESCE(NULLIF(:main_image, ''), main_image),
                            manufacturer_id_code = :manufacturer_id_code,
                            manufacturer_name = :manufacturer_name,
                            manufacturer_contact_person = :manufacturer_contact_person,
                            manufacturer_phone = :manufacturer_phone,
                            manufacturer_whatsapp = :manufacturer_whatsapp,
                            manufacturer_email = :manufacturer_email,
                            manufacturer_store_url = :manufacturer_store_url,
                            source_platform = :source_platform,
                            source_product_id = :source_product_id,
                            source_product_url = :source_product_url,
                            import_date = :import_date,
                            admin_status = :admin_status,
                            status = 'active',
                            updated_at = NOW()
                        WHERE id = :id
                    ");
                    $stmtUpdate->execute([
                        ':name'                        => $prod['name'],
                        ':slug'                        => $slug,
                        ':category_id'                 => $categoryId,
                        ':subcategory_id'              => $subcategoryId,
                        ':brand_id'                    => $brandId,
                        ':factory_id'                  => $assignedFactoryId,
                        ':weight'                      => $prod['weight'] ?: null,
                        ':variety'                     => $prod['variety'] ?: null,
                        ':description'                 => $prod['description'],
                        ':price'                       => $prod['tier1_price'] > 0 ? $prod['tier1_price'] : $prod['one_piece_price'],
                        ':sale_price'                  => $prod['one_piece_price'],
                        ':moq'                         => $prod['moq'],
                        ':stock'                       => $prod['available_qty'],
                        ':main_image'                  => $mainImagePath,
                        ':manufacturer_id_code'        => $prod['manufacturer_id_code'] ?: null,
                        ':manufacturer_name'           => $prod['manufacturer_name'] ?: null,
                        ':manufacturer_contact_person' => $prod['manufacturer_contact_person'] ?: null,
                        ':manufacturer_phone'          => $prod['manufacturer_phone'] ?: null,
                        ':manufacturer_whatsapp'       => $prod['manufacturer_whatsapp'] ?: null,
                        ':manufacturer_email'          => $prod['manufacturer_email'] ?: null,
                        ':manufacturer_store_url'      => $prod['manufacturer_store_url'] ?: null,
                        ':source_platform'             => $prod['source_platform'] ?: null,
                        ':source_product_id'           => $prod['source_product_id'] ?: null,
                        ':source_product_url'          => $prod['source_product_url'] ?: null,
                        ':import_date'                 => $prod['import_date'] ?: date('Y-m-d H:i:s'),
                        ':admin_status'                => $prod['admin_status'] ?: 'Active',
                        ':id'                          => $productId,
                    ]);
                    $updatedProducts++;
                } else {
                    $stmtInsert = $this->db->prepare("
                        INSERT INTO products (
                            name, slug, sku, category_id, subcategory_id, brand_id, factory_id,
                            weight, variety, description, price, sale_price, moq, stock, main_image, video_url,
                            manufacturer_id_code, manufacturer_name, manufacturer_contact_person, manufacturer_phone,
                            manufacturer_whatsapp, manufacturer_email, manufacturer_store_url, source_platform,
                            source_product_id, source_product_url, import_date, admin_status, status, created_at, updated_at
                        ) VALUES (
                            :name, :slug, :sku, :category_id, :subcategory_id, :brand_id, :factory_id,
                            :weight, :variety, :description, :price, :sale_price, :moq, :stock, :main_image, :video_url,
                            :manufacturer_id_code, :manufacturer_name, :manufacturer_contact_person, :manufacturer_phone,
                            :manufacturer_whatsapp, :manufacturer_email, :manufacturer_store_url, :source_platform,
                            :source_product_id, :source_product_url, :import_date, :admin_status, 'active', NOW(), NOW()
                        )
                    ");
                    $stmtInsert->execute([
                        ':name'                        => $prod['name'],
                        ':slug'                        => $slug,
                        ':sku'                         => $sku,
                        ':category_id'                 => $categoryId,
                        ':subcategory_id'              => $subcategoryId,
                        ':brand_id'                    => $brandId,
                        ':factory_id'                  => $assignedFactoryId,
                        ':weight'                      => $prod['weight'] ?: null,
                        ':variety'                     => $prod['variety'] ?: null,
                        ':description'                 => $prod['description'],
                        ':price'                       => $prod['tier1_price'] > 0 ? $prod['tier1_price'] : $prod['one_piece_price'],
                        ':sale_price'                  => $prod['one_piece_price'],
                        ':moq'                         => $prod['moq'],
                        ':stock'                       => $prod['available_qty'],
                        ':main_image'                  => $mainImagePath ?: 'assets/images/placeholder.jpg',
                        ':video_url'                   => $prod['video_url'] ?? null,
                        ':manufacturer_id_code'        => $prod['manufacturer_id_code'] ?: null,
                        ':manufacturer_name'           => $prod['manufacturer_name'] ?: null,
                        ':manufacturer_contact_person' => $prod['manufacturer_contact_person'] ?: null,
                        ':manufacturer_phone'          => $prod['manufacturer_phone'] ?: null,
                        ':manufacturer_whatsapp'       => $prod['manufacturer_whatsapp'] ?: null,
                        ':manufacturer_email'          => $prod['manufacturer_email'] ?: null,
                        ':manufacturer_store_url'      => $prod['manufacturer_store_url'] ?: null,
                        ':source_platform'             => $prod['source_platform'] ?: null,
                        ':source_product_id'           => $prod['source_product_id'] ?: null,
                        ':source_product_url'          => $prod['source_product_url'] ?: null,
                        ':import_date'                 => $prod['import_date'] ?: date('Y-m-d H:i:s'),
                        ':admin_status'                => $prod['admin_status'] ?: 'Active',
                    ]);
                    $productId = (int)$this->db->lastInsertId();
                    $createdProducts++;
                }

                // Sync Wholesale Tiers into `tiered_prices`
                $this->syncWholesaleTiers($productId, $prod);

                // Sync Specifications into `product_specifications`
                $this->syncProductSpecifications($productId, $prod);

                // Auto-populate filter option values from imported product data
                $this->syncFilterOptions($productId, $prod);
                (new \App\Services\FilterAttributeService())->syncProductSpecificationsToFilterAttributes($productId);

                // Process Main Cover Image in product_images table
                if (!empty($mainImagePath)) {
                    $chkM = $this->db->prepare("SELECT id FROM product_images WHERE product_id = ? AND (image_url = ? OR image_path = ?)");
                    $chkM->execute([$productId, $mainImagePath, $mainImagePath]);
                    if (!$chkM->fetch()) {
                        $stmtM = $this->db->prepare("INSERT INTO product_images (product_id, image_url, sort_order, is_primary) VALUES (?, ?, 0, 1)");
                        $stmtM->execute([$productId, $mainImagePath]);
                    }
                }

                // Process Additional Gallery Images
                if (!empty($prod['additional_images'])) {
                    $imgList = array_map('trim', explode(',', $prod['additional_images']));
                    foreach ($imgList as $gIdx => $gImgName) {
                        if (empty($gImgName)) continue;
                        $gImgPath = $this->processImageSource($gImgName, $extractedZipDir);
                        if ($gImgPath && $gImgPath !== $mainImagePath) {
                            $stmtG = $this->db->prepare("INSERT INTO product_images (product_id, image_url, sort_order, is_primary) VALUES (?, ?, ?, 0)");
                            $stmtG->execute([$productId, $gImgPath, $gIdx + 1]);
                        }
                    }
                }

                // Sync Variants into `product_variants`
                foreach ($prod['variants'] as $vData) {
                    $varSku = $vData['variant_sku'];
                    $vImgPath = !empty($vData['variant_image']) ? $this->processImageSource($vData['variant_image'], $extractedZipDir) : null;

                    $stmtVCheck = $this->db->prepare("SELECT id FROM product_variants WHERE variant_code = ?");
                    $stmtVCheck->execute([$varSku]);
                    $vExist = $stmtVCheck->fetch();

                    if ($vExist) {
                        $vId = (int)$vExist['id'];
                        $stmtVUpd = $this->db->prepare("
                            UPDATE product_variants SET
                                product_id = :product_id,
                                attribute_label = :attribute_label,
                                attribute_value = :attribute_value,
                                wholesale_price = :wholesale_price,
                                one_piece_price = :one_piece_price,
                                stock_quantity = :stock_quantity,
                                image_url = COALESCE(NULLIF(:image_url, ''), image_url),
                                is_active = 1,
                                updated_at = NOW()
                            WHERE id = :id
                        ");
                        $stmtVUpd->execute([
                            ':product_id'      => $productId,
                            ':attribute_label' => $vData['variation_type'],
                            ':attribute_value' => $vData['variation_value'],
                            ':wholesale_price' => $vData['variant_price'],
                            ':one_piece_price' => $vData['one_piece_price'],
                            ':stock_quantity'  => $vData['variant_stock'],
                            ':image_url'       => $vImgPath,
                            ':id'              => $vId,
                        ]);
                        $updatedVariants++;
                    } else {
                        $stmtVIns = $this->db->prepare("
                            INSERT INTO product_variants (
                                product_id, variant_code, attribute_label, attribute_value,
                                wholesale_price, one_piece_price, stock_quantity, image_url, is_active, created_at, updated_at
                            ) VALUES (
                                :product_id, :variant_code, :attribute_label, :attribute_value,
                                :wholesale_price, :one_piece_price, :stock_quantity, :image_url, 1, NOW(), NOW()
                            )
                        ");
                        $stmtVIns->execute([
                            ':product_id'      => $productId,
                            ':variant_code'    => $varSku,
                            ':attribute_label' => $vData['variation_type'],
                            ':attribute_value' => $vData['variation_value'],
                            ':wholesale_price' => $vData['variant_price'],
                            ':one_piece_price' => $vData['one_piece_price'],
                            ':stock_quantity'  => $vData['variant_stock'],
                            ':image_url'       => $vImgPath,
                        ]);
                        $createdVariants++;
                    }
                }
            }

            $this->db->commit();
        } catch (\Throwable $e) {
            $this->db->rollBack();
            return [
                'success' => false,
                'error'   => 'Database commit failed: ' . $e->getMessage()
            ];
        }

        return [
            'success'           => true,
            'created_products'  => $createdProducts,
            'updated_products'  => $updatedProducts,
            'created_variants'  => $createdVariants,
            'updated_variants'  => $updatedVariants,
            'created_factories' => $createdFactoriesCount,
            'skipped_products'  => $skippedProducts,
            'errors'            => $errorLogs,
        ];
    }

    /**
     * Save specifications from the 60-column bulk sheet.
     */
    private function syncProductSpecifications(int $productId, array $prod): void
    {
        $specMap = [
            'Jewellery Type'                    => $prod['jewellery_type'] ?? '',
            'Gender'                            => $prod['gender'] ?? '',
            'Brand Name'                        => $prod['brand'] ?? '',
            // Material/Metal Type — consolidated (Material col removed from template)
            'Material/Metal Type'               => $prod['material_type'] ?? '',
            'Metal Color'                       => $prod['metal_color'] ?? '',
            'Main Stone Type'                   => $prod['stone_type'] ?? '',
            'Size'                              => $prod['size'] ?? '',
            'Country of Origin'                 => $prod['country_of_origin'] ?? '',
            'Weight'                            => $prod['weight'] ?? '',
            'Variety'                           => $prod['variety'] ?? '',
            // Processing Technology / Technique / Treatment Process — split into 3 separate specs
            'Processing Technology'             => $prod['processing_technology'] ?? '',
            'Processing Technique'              => $prod['processing_technique'] ?? '',
            'Treatment Process'                 => $prod['treatment_process'] ?? '',
            'Style'                             => $prod['style'] ?? '',
            'Suitable For Gift Giving Occasion' => $prod['gift_occasion'] ?? '',
            'Item Number'                       => $prod['item_number'] ?? '',
            'Main Downstream Platform'          => $prod['downstream_platform'] ?? '',
            'Color'                             => $prod['color'] ?? '',
            'Popular Elements'                  => $prod['popular_elements'] ?? '',
            'Style Classification'              => $prod['style_classification'] ?? '',
            // Kind / Product Type — split into 2 separate specs (Jewellery Type col removed from template)
            'Kind'                              => $prod['kind'] ?? '',
            'Product Type'                      => $prod['product_type'] ?? '',
            'Chain Style'                       => $prod['chain_style'] ?? '',
            'Pendant Material'                  => $prod['pendant_material'] ?? '',
            'Trendy Element'                    => $prod['trendy_element'] ?? '',
            'Closure Type'                      => $prod['closure_type'] ?? '',
        ];

        $specsToSave = [];
        foreach ($specMap as $k => $v) {
            if (trim((string)$v) !== '') {
                $specsToSave[] = [
                    'key'   => $k,
                    'value' => trim((string)$v)
                ];
            }
        }

        $this->specModel->saveSpecifications($productId, $specsToSave);
    }

    /**
     * Auto-populate filter_attribute_options from a freshly imported product
     * and link the product to those options via product_filter_attribute_values.
     *
     * Maps parsed product fields to filter attribute slugs.  Comma-separated
     * values (e.g. "Vacuum Electroplating, Hand Weaving") are split into
     * individual filter options.
     */
    private function syncFilterOptions(int $productId, array $prod): void
    {
        /** @var \App\Services\FilterAttributeService $fs */
        $fs = new \App\Services\FilterAttributeService();

        // field-value  →  filter attribute slug mapping
        $fieldToSlug = [
            'category'              => 'category',
            'subcategory'           => 'subcategory',
            'jewellery_type'        => 'jewellery_type',
            'gender'                => 'gender',
            'material_type'         => 'material_metal_type',
            'metal_color'           => 'metal_color',
            'stone_type'            => 'main_stone_type',
            'country_of_origin'     => 'country_of_origin',
            'variety'               => 'variety',
            'processing_technology' => 'processing_technology',
            'processing_technique'  => 'processing_technique',
            'treatment_process'     => 'treatment_process',
            'style'                 => 'style',
            'style_classification'  => 'style_classification',
            'gift_occasion'         => 'suitable_for_gift_giving_occasion',
            'color'                 => 'color',
            'popular_elements'      => 'popular_elements',
            'kind'                  => 'kind',
            'product_type'          => 'product_type',
            'chain_style'           => 'chain_style',
            'pendant_material'      => 'pendant_material',
            'trendy_element'        => 'trendy_element',
            'closure_type'          => 'closure_type',
            'brand'                 => 'brand_name',
            // Admin-only fields
            'manufacturer_name'     => 'manufacturer_name',
            'source_platform'       => 'source_platform',
            'admin_status'          => 'status',
        ];

        // Collect attribute_id → [option_id, …] to save
        $attrData = [];

        foreach ($fieldToSlug as $field => $slug) {
            $rawValue = trim((string)($prod[$field] ?? ''));
            if ($rawValue === '') continue;

            // Look up the attribute
            $attr = $fs->getAttributeBySlug($slug);
            if (!$attr) continue;

            $attrId = (int)$attr['id'];

            // Split comma/semicolon-separated multi-values
            $parts = preg_split('/[,;]+/', $rawValue);
            foreach ($parts as $part) {
                $part = trim($part);
                $optId = $fs->getOrCreateOption($attrId, $part);
                if ($optId) {
                    $attrData[$attrId][] = $optId;
                }
            }
        }

        if (!empty($attrData)) {
            $fs->saveProductAttributeValues($productId, $attrData);
        }
    }

    /**
     * Save wholesale pricing tiers to `tiered_prices`.
     */
    private function syncWholesaleTiers(int $productId, array $prod): void
    {
        $stmtDel = $this->db->prepare("DELETE FROM tiered_prices WHERE product_id = ? AND (variant_id IS NULL OR variant_id = 0)");
        $stmtDel->execute([$productId]);

        $tiers = [];
        if (!empty($prod['tier1_qty']) && $prod['tier1_price'] > 0) {
            $parsedRange = $this->parseQtyRange($prod['tier1_qty'], 2, 35);
            $tiers[] = [
                'min_qty'    => $parsedRange['min_qty'],
                'max_qty'    => $parsedRange['max_qty'],
                'unit_price' => $prod['tier1_price'],
            ];
        }

        if (!empty($prod['tier2_qty']) && $prod['tier2_price'] > 0) {
            $parsedRange = $this->parseQtyRange($prod['tier2_qty'], 36, 149);
            $tiers[] = [
                'min_qty'    => $parsedRange['min_qty'],
                'max_qty'    => $parsedRange['max_qty'],
                'unit_price' => $prod['tier2_price'],
            ];
        }

        if (!empty($prod['tier3_qty']) && $prod['tier3_price'] > 0) {
            $parsedRange = $this->parseQtyRange($prod['tier3_qty'], 150, null);
            $tiers[] = [
                'min_qty'    => $parsedRange['min_qty'],
                'max_qty'    => $parsedRange['max_qty'],
                'unit_price' => $prod['tier3_price'],
            ];
        }

        $stmtIns = $this->db->prepare("INSERT INTO tiered_prices (product_id, min_qty, max_qty, unit_price) VALUES (?, ?, ?, ?)");
        foreach ($tiers as $t) {
            $stmtIns->execute([$productId, $t['min_qty'], $t['max_qty'], $t['unit_price']]);
        }
    }

    /**
     * Helper to parse Qty Range string into min_qty & max_qty.
     */
    private function parseQtyRange(string $rangeStr, int $defaultMin, ?int $defaultMax): array
    {
        $clean = trim(preg_replace('/[^0-9\-\>\<\+]/', '', $rangeStr));
        if (str_contains($clean, '-')) {
            $parts = explode('-', $clean);
            return [
                'min_qty' => max(1, (int)($parts[0] ?? $defaultMin)),
                'max_qty' => !empty($parts[1]) ? (int)$parts[1] : null,
            ];
        } elseif (str_contains($clean, '>') || str_contains($clean, '+')) {
            $minVal = (int)preg_replace('/[^0-9]/', '', $clean);
            return [
                'min_qty' => max(1, $minVal ?: $defaultMin),
                'max_qty' => null,
            ];
        }

        return [
            'min_qty' => $defaultMin,
            'max_qty' => $defaultMax
        ];
    }

    private function getCategoryCache(): array
    {
        if ($this->categoryCache === null) {
            $catModel = new Category();
            $allCats = $catModel->all();
            $this->categoryCache = [];
            foreach ($allCats as $c) {
                $this->categoryCache[strtolower($c['name'])] = (int)$c['id'];
            }
        }
        return $this->categoryCache;
    }

    private function getSubcategoryCache(): array
    {
        if ($this->subcategoryCache === null) {
            $subcatModel = new Subcategory();
            $allSubs = $subcatModel->all();
            $this->subcategoryCache = [];
            foreach ($allSubs as $sc) {
                $catId = $sc['category_id'] ?? 0;
                $key = $catId . '_' . strtolower($sc['name'] ?? '');
                $this->subcategoryCache[$key] = (int)($sc['id'] ?? 0);
            }
        }
        return $this->subcategoryCache;
    }

    private function getBrandCache(): array
    {
        if ($this->brandCache === null) {
            $brandModel = new Brand();
            $allBrands = $brandModel->all();
            $this->brandCache = [];
            foreach ($allBrands as $b) {
                $this->brandCache[strtolower($b['name'])] = (int)$b['id'];
            }
        }
        return $this->brandCache;
    }

    private function resolveOrCreateCategory(string $catName): int
    {
        $clean = trim($catName);
        $lower = strtolower($clean);
        $cache = $this->getCategoryCache();

        if (isset($cache[$lower])) {
            return $cache[$lower];
        }

        $catModel = new Category();
        $slug = $this->slugify($clean);
        $catId = $catModel->insert([
            'name'       => $clean,
            'slug'       => $slug,
            'status'     => 'active',
            'created_at' => date('Y-m-d H:i:s'),
            'updated_at' => date('Y-m-d H:i:s'),
        ]);

        $this->categoryCache[$lower] = $catId;
        return $catId;
    }

    private function resolveOrCreateSubcategory(int $categoryId, string $subcatName): int
    {
        $clean = trim($subcatName);
        $lower = strtolower($clean);
        $cacheKey = $categoryId . '_' . $lower;
        $cache = $this->getSubcategoryCache();

        if (isset($cache[$cacheKey])) {
            return $cache[$cacheKey];
        }

        $subcatModel = new Subcategory();
        $slug = $this->slugify($clean);
        $subId = $subcatModel->insert([
            'category_id' => $categoryId,
            'name'        => $clean,
            'slug'        => $slug,
            'status'      => 'active',
            'created_at'  => date('Y-m-d H:i:s'),
            'updated_at'  => date('Y-m-d H:i:s'),
        ]);

        $this->subcategoryCache[$cacheKey] = $subId;
        return $subId;
    }

    private function resolveOrCreateBrand(string $brandName): int
    {
        $clean = trim($brandName);
        $lower = strtolower($clean);
        $cache = $this->getBrandCache();

        if (isset($cache[$lower])) {
            return $cache[$lower];
        }

        $brandModel = new Brand();
        $slug = $this->slugify($clean);
        $brandId = $brandModel->insert([
            'name'       => $clean,
            'slug'       => $slug,
            'status'     => 'active',
            'created_at' => date('Y-m-d H:i:s'),
            'updated_at' => date('Y-m-d H:i:s'),
        ]);

        $this->brandCache[$lower] = $brandId;
        return $brandId;
    }

    private function processImageSource(string $imageInput, ?string $extractedZipDir): string
    {
        $clean = trim($imageInput);
        if (empty($clean)) {
            return '';
        }

        if (filter_var($clean, FILTER_VALIDATE_URL)) {
            return $clean;
        }

        if ($extractedZipDir) {
            $resolvedPath = $this->resolveZipImage($clean, $extractedZipDir);
            if ($resolvedPath) {
                return $resolvedPath;
            }
        }

        return $clean;
    }

    private function resolveZipImage(string $imageFilename, string $extractedZipDir): ?string
    {
        $cleanName = basename($imageFilename);
        $possiblePaths = [
            $extractedZipDir . '/' . $cleanName,
            $extractedZipDir . '/images/' . $cleanName,
            $extractedZipDir . '/img/' . $cleanName,
        ];

        foreach ($possiblePaths as $p) {
            if (file_exists($p)) {
                $targetDir = __DIR__ . '/../../public/uploads/products/';
                if (!is_dir($targetDir)) {
                    mkdir($targetDir, 0755, true);
                }
                $ext = pathinfo($cleanName, PATHINFO_EXTENSION) ?: 'jpg';
                $newFilename = 'bulk_' . time() . '_' . md5($cleanName) . '.' . $ext;
                $dest = $targetDir . $newFilename;
                copy($p, $dest);
                return 'uploads/products/' . $newFilename;
            }
        }

        return null;
    }

    private function extractZipImages(string $zipFilePath): ?string
    {
        $zip = new ZipArchive();
        if ($zip->open($zipFilePath) === true) {
            $extractPath = sys_get_temp_dir() . '/importwala_zip_' . time();
            @mkdir($extractPath, 0755, true);
            $zip->extractTo($extractPath);
            $zip->close();
            return $extractPath;
        }
        return null;
    }

    private function slugify(string $text): string
    {
        $text = preg_replace('~[^\pL\d]+~u', '-', $text);
        $text = iconv('utf-8', 'us-ascii//TRANSLIT', $text);
        $text = preg_replace('~[^-\w]+~', '', $text);
        $text = trim($text, '-');
        $text = preg_replace('~-+~', '-', $text);
        $text = strtolower($text);
        return empty($text) ? 'product-' . time() : $text;
    }
}
