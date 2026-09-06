<?php

namespace App\Services;

use App\Core\Database;
use App\Models\Product;
use App\Models\ProductVariant;
use App\Models\ProductSpecification;
use App\Models\Category;
use App\Models\Subcategory;
use App\Models\Brand;
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
        'Material',                                                            // 12
        'Country of Origin',                                                   // 13
        'One Piece Price',                                                     // 14
        'Wholesale Tier 1 Qty Range',                                          // 15
        'Wholesale Tier 1 Price',                                              // 16
        'Wholesale Tier 2 Qty Range',                                          // 17
        'Wholesale Tier 2 Price',                                              // 18
        'Wholesale Tier 3 Qty Range',                                          // 19
        'Wholesale Tier 3 Price',                                              // 20
        'Main Product Image',                                                  // 21
        'Additional Image 1',                                                  // 22
        'Additional Image 2',                                                  // 23
        'Additional Image 3',                                                  // 24
        'Additional Image 4',                                                  // 25
        'Product Video URL',                                                   // 26
        'Variation Type',                                                      // 27
        'Variation Value',                                                     // 28
        'Variation SKU',                                                       // 29
        'Variation Price',                                                     // 30
        'Variation Stock',                                                     // 31
        'Variation Image',                                                     // 32
        'Processing Technology / Processing Technique / Treatment Process',     // 33
        'Style',                                                               // 34
        'Suitable For Gift Giving Occasion',                                   // 35
        'Item Number',                                                         // 36
        'Main Downstream Platform',                                            // 37
        'Color',                                                               // 38
        'Popular Elements',                                                    // 39
        'Style Classification',                                                // 40
        'Kind/Product Type/Jewellery Type',                                     // 41
        'Chain Style',                                                         // 42
        'Pendant Material',                                                    // 43
        'Trendy Element',                                                      // 44
        'Closure Type',                                                        // 45
    ];

    public function __construct()
    {
        $this->db = Database::getInstance();
        $this->productModel = new Product();
        $this->variantModel = new ProductVariant();
        $this->specModel = new ProductSpecification();
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
            $sheet->getStyle($colLetter . '1')->getFill()->setFillType(\PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID)
                ->getStartColor()->setARGB('FFF05A29');
            $sheet->getStyle($colLetter . '1')->getFont()->getColor()->setARGB('FFFFFFFF');
        }

        // Sample Data Row 1 (Product with 1st Variant)
        $sampleRow1 = [
            'Cross-Border Tiger Eye Leather Bracelet',                        // 0: Product Name
            'JWL-BRC-001',                                                    // 1: Product SKU
            'Fashion Jewellery',                                             // 2: Category
            'Bracelets & Bangles',                                           // 3: Subcategory
            'Hand-woven genuine leather bracelet with natural Tiger Eye stone and stainless steel magnetic clasp.', // 4: Description
            'Bracelet',                                                      // 5: Jewellery Type
            'Men',                                                           // 6: Gender
            'ImportWale OEM',                                                // 7: Brand Name
            '316L Stainless Steel & Leather',                                // 8: Material/Metal Type
            'Silver / Black PVD',                                            // 9: Metal Color
            'Natural Tiger Eye Stone',                                       // 10: Main Stone Type
            '21 cm',                                                         // 11: Size
            'Stainless Steel, Leather, Tiger Eye',                           // 12: Material
            'China',                                                         // 13: Country of Origin
            '299.00',                                                        // 14: One Piece Price
            '2-35',                                                          // 15: Wholesale Tier 1 Qty Range
            '145.00',                                                        // 16: Wholesale Tier 1 Price
            '36-149',                                                        // 17: Wholesale Tier 2 Qty Range
            '125.00',                                                        // 18: Wholesale Tier 2 Price
            '150+',                                                          // 19: Wholesale Tier 3 Qty Range
            '99.00',                                                         // 20: Wholesale Tier 3 Price
            'https://images.importwale.com/products/jwl-brc-001-main.jpg',     // 21: Main Product Image
            'https://images.importwale.com/products/jwl-brc-001-1.jpg',        // 22: Additional Image 1
            'https://images.importwale.com/products/jwl-brc-001-2.jpg',        // 23: Additional Image 2
            'https://images.importwale.com/products/jwl-brc-001-3.jpg',        // 24: Additional Image 3
            'https://images.importwale.com/products/jwl-brc-001-4.jpg',        // 25: Additional Image 4
            'https://media.importwale.com/videos/jwl-brc-001.mp4',             // 26: Product Video URL
            'Color',                                                         // 27: Variation Type
            'Brown Leather - Gold Clasp',                                    // 28: Variation Value
            'JWL-BRC-001-BRN-GLD',                                           // 29: Variation SKU
            '145.00',                                                        // 30: Variation Price
            '500',                                                           // 31: Variation Stock
            'https://images.importwale.com/products/jwl-brc-001-brn-gld.jpg', // 32: Variation Image
            'Vacuum Electroplating & Hand Weaving',                          // 33: Processing Tech
            'Vintage / Punk',                                                // 34: Style
            "Birthday, Father's Day",                                        // 35: Gift Occasion
            'JWL-2026-BRC01',                                                // 36: Item Number
            'Amazon, Flipkart, Meesho',                                      // 37: Downstream Platform
            'Brown / Tiger Eye',                                             // 38: Color
            'Geometry, Leather Weave',                                       // 39: Popular Elements
            'Fashion Commuter',                                              // 40: Style Classification
            "Men's Leather Bracelet",                                        // 41: Kind/Product Type
            'Braided Rope Chain',                                            // 42: Chain Style
            'N/A',                                                           // 43: Pendant Material
            'Retro Braided Leather',                                         // 44: Trendy Element
            'Magnetic Clasp',                                                // 45: Closure Type
        ];

        // Sample Data Row 2 (2nd Variant for same product)
        $sampleRow2 = [
            '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '',
            'Color',                                                         // 27: Variation Type
            'Black Leather - Silver Clasp',                                  // 28: Variation Value
            'JWL-BRC-001-BLK-SLV',                                           // 29: Variation SKU
            '145.00',                                                        // 30: Variation Price
            '350',                                                           // 31: Variation Stock
            'https://images.importwale.com/products/jwl-brc-001-blk-slv.jpg', // 32: Variation Image
            '', '', '', '', '', '', '', '', '', '', '', '', ''
        ];

        foreach ($sampleRow1 as $cIdx => $val) {
            $colLetter = \PhpOffice\PhpSpreadsheet\Cell\Coordinate::stringFromColumnIndex($cIdx + 1);
            $sheet->setCellValue($colLetter . '2', $val);
        }
        foreach ($sampleRow2 as $cIdx => $val) {
            $colLetter = \PhpOffice\PhpSpreadsheet\Cell\Coordinate::stringFromColumnIndex($cIdx + 1);
            $sheet->setCellValue($colLetter . '3', $val);
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
     * Parse spreadsheet file & validate contents.
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
                'error' => 'Unable to parse spreadsheet file: ' . $e->getMessage()
            ];
        }

        if (count($rows) <= 1) {
            return [
                'success' => false,
                'error' => 'The uploaded file is empty or missing data rows.'
            ];
        }

        // Skip header
        $headerRow = array_values(array_shift($rows));
        
        $parsedRows = [];
        $fileVariantSkus = [];
        $rowIndex = 2; // 1-indexed Excel row counter

        // Cache category and brand lookups
        $categoryCache = $this->getCategoryCache();
        $subcategoryCache = $this->getSubcategoryCache();
        $brandCache = $this->getBrandCache();

        $currentProductGroup = null;
        $groupedProducts = [];

        foreach ($rows as $rowMap) {
            $row = array_values($rowMap);
            // Check empty row
            if (empty(array_filter($row, fn($val) => trim((string)$val) !== ''))) {
                $rowIndex++;
                continue;
            }

            // Extract fields based on 46-column schema
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

            // Group additional images from columns 22..25
            $addImgs = array_filter([
                trim((string)($row[22] ?? '')),
                trim((string)($row[23] ?? '')),
                trim((string)($row[24] ?? '')),
                trim((string)($row[25] ?? '')),
            ]);

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
                    'material_type'           => trim((string)($row[8] ?? '')),
                    'metal_color'             => trim((string)($row[9] ?? '')),
                    'stone_type'              => trim((string)($row[10] ?? '')),
                    'size'                    => trim((string)($row[11] ?? '')),
                    'material'                => trim((string)($row[12] ?? '')),
                    'country_of_origin'       => trim((string)($row[13] ?? '')),
                    'one_piece_price'         => (float)($row[14] ?? 0),
                    'tier1_qty'               => trim((string)($row[15] ?? '')),
                    'tier1_price'             => (float)($row[16] ?? 0),
                    'tier2_qty'               => trim((string)($row[17] ?? '')),
                    'tier2_price'             => (float)($row[18] ?? 0),
                    'tier3_qty'               => trim((string)($row[19] ?? '')),
                    'tier3_price'             => (float)($row[20] ?? 0),
                    'main_image'              => trim((string)($row[21] ?? '')),
                    'additional_images'       => implode(',', $addImgs),
                    'video_url'               => trim((string)($row[26] ?? '')),
                    'processing_tech'         => trim((string)($row[33] ?? '')),
                    'style'                   => trim((string)($row[34] ?? '')),
                    'gift_occasion'           => trim((string)($row[35] ?? '')),
                    'item_number'             => trim((string)($row[36] ?? '')),
                    'downstream_platform'    => trim((string)($row[37] ?? '')),
                    'color'                   => trim((string)($row[38] ?? '')),
                    'popular_elements'        => trim((string)($row[39] ?? '')),
                    'style_classification'    => trim((string)($row[40] ?? '')),
                    'kind_product_type'       => trim((string)($row[41] ?? '')),
                    'chain_style'             => trim((string)($row[42] ?? '')),
                    'pendant_material'        => trim((string)($row[43] ?? '')),
                    'trendy_element'          => trim((string)($row[44] ?? '')),
                    'closure_type'            => trim((string)($row[45] ?? '')),
                    'moq'                     => 1,
                    'available_qty'           => 100,
                    'variants'                => [],
                    'rows'                    => [],
                    'errors'                  => [],
                    'warnings'                => [],
                ];
            } else {
                // Forward fill product level attributes if subsequent row has non-empty fields
                if (empty($groupedProducts[$productSku]['name']) && !empty($productName)) {
                    $groupedProducts[$productSku]['name'] = $productName;
                }
                if (empty($groupedProducts[$productSku]['main_image']) && !empty($row[21])) {
                    $groupedProducts[$productSku]['main_image'] = trim((string)$row[21]);
                }
            }

            $pGroup = &$groupedProducts[$productSku];
            $pGroup['rows'][] = $rowIndex;

            // Variant Level Data (Columns 27..32)
            $varType  = trim((string)($row[27] ?? ''));
            $varVal   = trim((string)($row[28] ?? ''));
            $varSku   = strtoupper(trim((string)($row[29] ?? '')));
            $varPrice = (float)($row[30] ?? 0);
            $varStock = (int)($row[31] ?? $pGroup['available_qty']);
            $varImg   = trim((string)($row[32] ?? ''));

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
            ],
            'products'         => array_values($groupedProducts),
            'extracted_zip_dir'=> $extractedZipDir,
        ];
    }

    /**
     * Execute Database Commit for validated products.
     */
    public function commitImport(array $productsData, ?string $extractedZipDir = null): array
    {
        $createdProducts = 0;
        $updatedProducts = 0;
        $createdVariants = 0;
        $updatedVariants = 0;
        $skippedProducts = 0;
        $errorLogs = [];

        $this->db->beginTransaction();

        try {
            foreach ($productsData as $prod) {
                if (($prod['status'] ?? '') === 'error') {
                    $skippedProducts++;
                    $errorLogs[] = [
                        'sku' => $prod['product_sku'],
                        'name' => $prod['name'],
                        'reason' => implode('; ', $prod['errors'] ?? ['Validation failed.'])
                    ];
                    continue;
                }

                $sku = $prod['product_sku'];
                $categoryId = $this->resolveOrCreateCategory($prod['category']);
                $subcategoryId = !empty($prod['subcategory']) ? $this->resolveOrCreateSubcategory($categoryId, $prod['subcategory']) : null;
                $brandId = !empty($prod['brand']) ? $this->resolveOrCreateBrand($prod['brand']) : null;

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
                            description = :description,
                            price = :price,
                            sale_price = :sale_price,
                            moq = :moq,
                            stock = :stock,
                            main_image = COALESCE(NULLIF(:main_image, ''), main_image),
                            status = 'active',
                            updated_at = NOW()
                        WHERE id = :id
                    ");
                    $stmtUpdate->execute([
                        ':name'           => $prod['name'],
                        ':slug'           => $slug,
                        ':category_id'    => $categoryId,
                        ':subcategory_id' => $subcategoryId,
                        ':brand_id'       => $brandId,
                        ':description'    => $prod['description'],
                        ':price'          => $prod['tier1_price'] > 0 ? $prod['tier1_price'] : $prod['one_piece_price'],
                        ':sale_price'     => $prod['one_piece_price'],
                        ':moq'            => $prod['moq'],
                        ':stock'          => $prod['available_qty'],
                        ':main_image'     => $mainImagePath,
                        ':id'             => $productId,
                    ]);
                    $updatedProducts++;
                } else {
                    $stmtInsert = $this->db->prepare("
                        INSERT INTO products (
                            name, slug, sku, category_id, subcategory_id, brand_id, description,
                            price, sale_price, moq, stock, main_image, video_url, status, created_at, updated_at
                        ) VALUES (
                            :name, :slug, :sku, :category_id, :subcategory_id, :brand_id, :description,
                            :price, :sale_price, :moq, :stock, :main_image, :video_url, 'active', NOW(), NOW()
                        )
                    ");
                    $stmtInsert->execute([
                        ':name'           => $prod['name'],
                        ':slug'           => $slug,
                        ':sku'            => $sku,
                        ':category_id'    => $categoryId,
                        ':subcategory_id' => $subcategoryId,
                        ':brand_id'       => $brandId,
                        ':description'    => $prod['description'],
                        ':price'          => $prod['tier1_price'] > 0 ? $prod['tier1_price'] : $prod['one_piece_price'],
                        ':sale_price'     => $prod['one_piece_price'],
                        ':moq'            => $prod['moq'],
                        ':stock'          => $prod['available_qty'],
                        ':main_image'     => $mainImagePath ?: 'assets/images/placeholder.jpg',
                        ':video_url'      => $prod['video_url'] ?? null,
                    ]);
                    $productId = (int)$this->db->lastInsertId();
                    $createdProducts++;
                }

                // Sync Wholesale Tiers into `tiered_prices`
                $this->syncWholesaleTiers($productId, $prod);

                // Sync Specifications into `product_specifications`
                $this->syncProductSpecifications($productId, $prod);

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
                'error' => 'Database commit failed: ' . $e->getMessage()
            ];
        }

        return [
            'success'          => true,
            'created_products' => $createdProducts,
            'updated_products' => $updatedProducts,
            'created_variants' => $createdVariants,
            'updated_variants' => $updatedVariants,
            'skipped_products' => $skippedProducts,
            'errors'           => $errorLogs,
        ];
    }

    /**
     * Save specifications from the 46-column bulk sheet.
     */
    private function syncProductSpecifications(int $productId, array $prod): void
    {
        $specMap = [
            'Jewellery Type'                    => $prod['jewellery_type'] ?? '',
            'Gender'                            => $prod['gender'] ?? '',
            'Brand Name'                        => $prod['brand'] ?? '',
            'Material/Metal Type'               => $prod['material_type'] ?? '',
            'Metal Color'                       => $prod['metal_color'] ?? '',
            'Main Stone Type'                   => $prod['stone_type'] ?? '',
            'Size'                              => $prod['size'] ?? '',
            'Material'                          => $prod['material'] ?? '',
            'Country of Origin'                 => $prod['country_of_origin'] ?? '',
            'Processing Technology / Technique' => $prod['processing_tech'] ?? '',
            'Style'                             => $prod['style'] ?? '',
            'Suitable For Gift Giving Occasion' => $prod['gift_occasion'] ?? '',
            'Item Number'                       => $prod['item_number'] ?? '',
            'Main Downstream Platform'          => $prod['downstream_platform'] ?? '',
            'Color'                             => $prod['color'] ?? '',
            'Popular Elements'                  => $prod['popular_elements'] ?? '',
            'Style Classification'              => $prod['style_classification'] ?? '',
            'Kind / Product Type'               => $prod['kind_product_type'] ?? '',
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
                'min_qty' => $minVal > 0 ? $minVal : $defaultMin,
                'max_qty' => null,
            ];
        }

        return [
            'min_qty' => $defaultMin,
            'max_qty' => $defaultMax,
        ];
    }

    /**
     * Helper to resolve image path from ZIP archive or URL.
     */
    private function processImageSource(string $imgInput, ?string $extractedZipDir): ?string
    {
        $imgInput = trim($imgInput);
        if (empty($imgInput)) return null;

        if (filter_var($imgInput, FILTER_VALIDATE_URL)) {
            return $imgInput;
        }

        if ($extractedZipDir) {
            $matchedFile = $this->resolveZipImage($imgInput, $extractedZipDir);
            if ($matchedFile) {
                $targetDir = ROOT_PATH . '/public/uploads/products/';
                if (!is_dir($targetDir)) {
                    mkdir($targetDir, 0755, true);
                }
                $ext = pathinfo($matchedFile, PATHINFO_EXTENSION) ?: 'jpg';
                $newFilename = 'bulk_' . time() . '_' . md5($imgInput) . '.' . $ext;
                copy($matchedFile, $targetDir . $newFilename);
                return 'uploads/products/' . $newFilename;
            }
        }

        // If file already exists relative to public/
        if (file_exists(ROOT_PATH . '/public/' . ltrim($imgInput, '/'))) {
            return ltrim($imgInput, '/');
        }

        return null;
    }

    /**
     * Search extracted ZIP directory for image filename.
     */
    private function resolveZipImage(string $filename, string $extractedDir): ?string
    {
        $targetName = strtolower(basename($filename));
        $dirIterator = new \RecursiveIteratorIterator(new \RecursiveDirectoryIterator($extractedDir));
        foreach ($dirIterator as $file) {
            if ($file->isFile() && strtolower($file->getFilename()) === $targetName) {
                return $file->getPathname();
            }
        }
        return null;
    }

    /**
     * Extract companion ZIP file to temp directory.
     */
    private function extractZipImages(string $zipFilePath): ?string
    {
        $zip = new ZipArchive();
        if ($zip->open($zipFilePath) === true) {
            $extractPath = sys_get_temp_dir() . '/importwala_zip_' . time() . '_' . rand(1000, 9999);
            mkdir($extractPath, 0755, true);
            $zip->extractTo($extractPath);
            $zip->close();
            return $extractPath;
        }
        return null;
    }

    private function getCategoryCache(): array
    {
        if ($this->categoryCache !== null) {
            return $this->categoryCache;
        }
        $stmt = $this->db->query("SELECT id, name FROM categories");
        $cats = $stmt->fetchAll();
        $this->categoryCache = [];
        foreach ($cats as $c) {
            $this->categoryCache[strtolower(trim($c['name']))] = (int)$c['id'];
        }
        return $this->categoryCache;
    }

    private function getSubcategoryCache(): array
    {
        if ($this->subcategoryCache !== null) {
            return $this->subcategoryCache;
        }
        $stmt = $this->db->query("SELECT id, category_id, name FROM subcategories");
        $subcats = $stmt->fetchAll();
        $this->subcategoryCache = [];
        foreach ($subcats as $sc) {
            $key = (int)$sc['category_id'] . '_' . strtolower(trim($sc['name']));
            $this->subcategoryCache[$key] = (int)$sc['id'];
        }
        return $this->subcategoryCache;
    }

    private function getBrandCache(): array
    {
        if ($this->brandCache !== null) {
            return $this->brandCache;
        }
        $stmt = $this->db->query("SELECT id, name FROM brands");
        $brands = $stmt->fetchAll();
        $this->brandCache = [];
        foreach ($brands as $b) {
            $this->brandCache[strtolower(trim($b['name']))] = (int)$b['id'];
        }
        return $this->brandCache;
    }

    private function resolveOrCreateCategory(string $catName): int
    {
        $catLower = strtolower(trim($catName));
        $cache = $this->getCategoryCache();
        if (isset($cache[$catLower])) {
            return $cache[$catLower];
        }

        $slug = $this->slugify($catName);

        // Check if category with matching slug or lowercase name already exists
        $stmtCheck = $this->db->prepare("SELECT id, name FROM categories WHERE slug = ? OR LOWER(name) = ?");
        $stmtCheck->execute([$slug, $catLower]);
        $existing = $stmtCheck->fetch();
        if ($existing) {
            $existingId = (int)$existing['id'];
            $this->categoryCache[$catLower] = $existingId;
            $this->categoryCache[strtolower(trim($existing['name']))] = $existingId;
            return $existingId;
        }

        try {
            $stmt = $this->db->prepare("INSERT INTO categories (name, slug, status, created_at, updated_at) VALUES (?, ?, 'active', NOW(), NOW())");
            $stmt->execute([$catName, $slug]);
            $newId = (int)$this->db->lastInsertId();
        } catch (\Throwable $e) {
            $slugUnique = $slug . '-' . substr(md5(uniqid()), 0, 4);
            $stmt = $this->db->prepare("INSERT INTO categories (name, slug, status, created_at, updated_at) VALUES (?, ?, 'active', NOW(), NOW())");
            $stmt->execute([$catName, $slugUnique]);
            $newId = (int)$this->db->lastInsertId();
        }

        $this->categoryCache[$catLower] = $newId;
        return $newId;
    }

    private function resolveOrCreateSubcategory(int $categoryId, string $subcatName): int
    {
        $subcatLower = strtolower(trim($subcatName));
        $cache = $this->getSubcategoryCache();
        $key = $categoryId . '_' . $subcatLower;
        if (isset($cache[$key])) {
            return $cache[$key];
        }

        $slug = $this->slugify($subcatName);

        // Check if subcategory with matching slug or lowercase name already exists for this category
        $stmtCheck = $this->db->prepare("SELECT id, name FROM subcategories WHERE category_id = ? AND (slug = ? OR LOWER(name) = ?)");
        $stmtCheck->execute([$categoryId, $slug, $subcatLower]);
        $existing = $stmtCheck->fetch();
        if ($existing) {
            $existingId = (int)$existing['id'];
            $this->subcategoryCache[$key] = $existingId;
            return $existingId;
        }

        try {
            $stmt = $this->db->prepare("INSERT INTO subcategories (category_id, name, slug, status, created_at, updated_at) VALUES (?, ?, ?, 'active', NOW(), NOW())");
            $stmt->execute([$categoryId, $subcatName, $slug]);
            $newId = (int)$this->db->lastInsertId();
        } catch (\Throwable $e) {
            $slugUnique = $slug . '-' . substr(md5(uniqid()), 0, 4);
            $stmt = $this->db->prepare("INSERT INTO subcategories (category_id, name, slug, status, created_at, updated_at) VALUES (?, ?, ?, 'active', NOW(), NOW())");
            $stmt->execute([$categoryId, $subcatName, $slugUnique]);
            $newId = (int)$this->db->lastInsertId();
        }

        $this->subcategoryCache[$key] = $newId;
        return $newId;
    }

    private function resolveOrCreateBrand(string $brandName): int
    {
        $bLower = strtolower(trim($brandName));
        $cache = $this->getBrandCache();
        if (isset($cache[$bLower])) {
            return $cache[$bLower];
        }

        $slug = $this->slugify($brandName);

        // Check if brand with matching slug or lowercase name already exists
        $stmtCheck = $this->db->prepare("SELECT id, name FROM brands WHERE slug = ? OR LOWER(name) = ?");
        $stmtCheck->execute([$slug, $bLower]);
        $existing = $stmtCheck->fetch();
        if ($existing) {
            $existingId = (int)$existing['id'];
            $this->brandCache[$bLower] = $existingId;
            return $existingId;
        }

        try {
            $stmt = $this->db->prepare("INSERT INTO brands (name, slug, status, created_at, updated_at) VALUES (?, ?, 'active', NOW(), NOW())");
            $stmt->execute([$brandName, $slug]);
            $newId = (int)$this->db->lastInsertId();
        } catch (\Throwable $e) {
            $slugUnique = $slug . '-' . substr(md5(uniqid()), 0, 4);
            $stmt = $this->db->prepare("INSERT INTO brands (name, slug, status, created_at, updated_at) VALUES (?, ?, 'active', NOW(), NOW())");
            $stmt->execute([$brandName, $slugUnique]);
            $newId = (int)$this->db->lastInsertId();
        }

        $this->brandCache[$bLower] = $newId;
        return $newId;
    }

    private function parseBool($val): int
    {
        $v = strtolower(trim((string)$val));
        return in_array($v, ['1', 'true', 'yes', 'y'], true) ? 1 : 0;
    }

    private function slugify(string $text): string
    {
        $text = preg_replace('~[^\pL\d]+~u', '-', $text);
        $text = iconv('utf-8', 'us-ascii//TRANSLIT', $text);
        $text = preg_replace('~[^-\w]+~', '', $text);
        $text = trim($text, '-');
        $text = preg_replace('~-+~', '-', $text);
        return strtolower($text ?: 'n-a');
    }
}
