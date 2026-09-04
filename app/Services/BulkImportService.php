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
        'Product Name',                          // 0
        'Product SKU',                           // 1
        'Category',                              // 2
        'Subcategory',                           // 3
        'Description',                          // 4
        'Jewellery Type',                        // 5
        'Gender',                                // 6
        'Brand Name',                            // 7
        'Material/Metal Type',                   // 8
        'Metal Purity',                          // 9
        'Metal Color',                           // 10
        'Plating Type',                          // 11
        'Main Stone Type',                       // 12
        'Stone Name',                            // 13
        'Stone Color',                           // 14
        'Stone Shape',                           // 15
        'Stone Weight/Carat',                    // 16
        'Number of Stones',                      // 17
        'Gross Weight',                          // 18
        'Net Weight',                            // 19
        'Length',                                // 20
        'Width',                                 // 21
        'Height/Thickness',                      // 22
        'Ring Size',                             // 23
        'Certification Available (Y/N)',         // 24
        'Certificate Type',                      // 25
        'Currency',                              // 26
        'One Piece Price',                       // 27
        'Wholesale Tier 1 Qty Range',            // 28
        'Wholesale Tier 1 Price',                // 29
        'Wholesale Tier 2 Qty Range',            // 30
        'Wholesale Tier 2 Price',                // 31
        'Wholesale Tier 3 Qty Range',            // 32
        'Wholesale Tier 3 Price',                // 33
        'MOQ',                                   // 34
        'Available Quantity',                    // 35
        'Price Negotiable (Y/N)',                // 36
        'OEM Available (Y/N)',                   // 37
        'ODM Available (Y/N)',                   // 38
        'Customization Available (Y/N)',         // 39
        'Sample Available (Y/N)',                // 40
        'Sample Price',                          // 41
        'Production Lead Time',                  // 42
        'Production Capacity',                   // 43
        'Packaging Details',                     // 44
        'Country of Origin',                     // 45
        'Main Product Image',                    // 46
        'Additional Image 1-4',                  // 47
        'Product Video URL',                     // 48
        'Variation Type',                        // 49
        'Variation Value',                       // 50
        'Variation SKU',                         // 51
        'Variation Price',                       // 52
        'Variation Stock',                       // 53
        'Variation Image',                       // 54
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

        // Sample Data Rows (1 Product with 2 Variants)
        $sampleRow1 = [
            'Royal Crystal Drop Earrings',       // Product Name
            'MUD-ER-801',                        // Product SKU
            'Jewellery',                         // Category
            'Earrings',                          // Subcategory
            'Handcrafted 18K gold plated crystal drop earrings with sparkling zircon stones.', // Description
            'Earrings',                          // Jewellery Type
            'Women',                             // Gender
            'Mudsor Enterprise',                // Brand Name
            'Brass Alloy',                       // Material/Metal Type
            '18K Gold Plated',                   // Metal Purity
            'Yellow Gold',                       // Metal Color
            'Micro Plating',                     // Plating Type
            'Cubic Zirconia',                    // Main Stone Type
            'Royal Zircon',                      // Stone Name
            'Emerald Green',                     // Stone Color
            'Teardrop',                          // Stone Shape
            '2.5 Carat',                         // Stone Weight/Carat
            '12',                                // Number of Stones
            '15.5 g',                            // Gross Weight
            '12.0 g',                            // Net Weight
            '45 mm',                             // Length
            '15 mm',                             // Width
            '5 mm',                              // Height/Thickness
            '',                                  // Ring Size (N/A for earrings)
            'Y',                                 // Certification Available (Y/N)
            'Hallmark ISO 9001',                 // Certificate Type
            'INR',                               // Currency
            '999.00',                            // One Piece Price
            '2-9',                               // Tier 1 Range
            '899.00',                            // Tier 1 Price
            '10-49',                             // Tier 2 Range
            '799.00',                            // Tier 2 Price
            '>=50',                              // Tier 3 Range
            '699.00',                            // Tier 3 Price
            '2',                                 // MOQ
            '500',                               // Available Quantity
            'Y',                                 // Price Negotiable
            'Y',                                 // OEM Available
            'Y',                                 // ODM Available
            'Y',                                 // Customization Available
            'Y',                                 // Sample Available
            '499.00',                            // Sample Price
            '7-10 Days',                         // Production Lead Time
            '10,000 pcs / month',                // Production Capacity
            'Velvet Box Packaging',              // Packaging Details
            'India',                             // Country of Origin
            'earrings_green_main.jpg',           // Main Product Image
            'earrings_side1.jpg, earrings_back.jpg', // Additional Image 1-4
            'https://youtube.com/watch?v=sample',// Product Video URL
            'Color',                             // Variation Type
            'Emerald Green',                     // Variation Value
            'MUD-ER-801-GRN',                    // Variation SKU
            '899.00',                            // Variation Price
            '250',                               // Variation Stock
            'earrings_green_main.jpg',           // Variation Image
        ];

        $sampleRow2 = [
            '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '',
            'Color',                             // Variation Type
            'Ruby Red',                          // Variation Value
            'MUD-ER-801-RED',                    // Variation SKU
            '949.00',                            // Variation Price
            '250',                               // Variation Stock
            'earrings_red_main.jpg',             // Variation Image
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

            // Extract fields
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

            if (!isset($groupedProducts[$productSku])) {
                $groupedProducts[$productSku] = [
                    'product_sku'          => $productSku,
                    'name'                 => $productName,
                    'category'             => trim((string)($row[2] ?? '')),
                    'subcategory'          => trim((string)($row[3] ?? '')),
                    'description'          => trim((string)($row[4] ?? '')),
                    'jewellery_type'       => trim((string)($row[5] ?? '')),
                    'gender'               => trim((string)($row[6] ?? '')),
                    'brand'                => trim((string)($row[7] ?? '')),
                    'material_type'        => trim((string)($row[8] ?? '')),
                    'metal_purity'         => trim((string)($row[9] ?? '')),
                    'metal_color'          => trim((string)($row[10] ?? '')),
                    'plating_type'         => trim((string)($row[11] ?? '')),
                    'stone_type'           => trim((string)($row[12] ?? '')),
                    'stone_name'           => trim((string)($row[13] ?? '')),
                    'stone_color'          => trim((string)($row[14] ?? '')),
                    'stone_shape'          => trim((string)($row[15] ?? '')),
                    'stone_carat'          => trim((string)($row[16] ?? '')),
                    'stone_count'          => trim((string)($row[17] ?? '')),
                    'gross_weight'         => trim((string)($row[18] ?? '')),
                    'net_weight'           => trim((string)($row[19] ?? '')),
                    'length'               => trim((string)($row[20] ?? '')),
                    'width'                => trim((string)($row[21] ?? '')),
                    'height'               => trim((string)($row[22] ?? '')),
                    'ring_size'            => trim((string)($row[23] ?? '')),
                    'certification_available' => $this->parseBool($row[24] ?? 'N'),
                    'certificate_type'     => trim((string)($row[25] ?? '')),
                    'currency'             => trim((string)($row[26] ?? 'INR')),
                    'one_piece_price'      => (float)($row[27] ?? 0),
                    'tier1_qty'            => trim((string)($row[28] ?? '')),
                    'tier1_price'          => (float)($row[29] ?? 0),
                    'tier2_qty'            => trim((string)($row[30] ?? '')),
                    'tier2_price'          => (float)($row[31] ?? 0),
                    'tier3_qty'            => trim((string)($row[32] ?? '')),
                    'tier3_price'          => (float)($row[33] ?? 0),
                    'moq'                  => max(1, (int)($row[34] ?? 1)),
                    'available_qty'        => (int)($row[35] ?? 100),
                    'price_negotiable'     => $this->parseBool($row[36] ?? 'N'),
                    'oem_available'        => $this->parseBool($row[37] ?? 'N'),
                    'odm_available'        => $this->parseBool($row[38] ?? 'N'),
                    'customization_available' => $this->parseBool($row[39] ?? 'N'),
                    'sample_available'     => $this->parseBool($row[40] ?? 'N'),
                    'sample_price'         => (float)($row[41] ?? 0),
                    'production_lead_time' => trim((string)($row[42] ?? '')),
                    'production_capacity'  => trim((string)($row[43] ?? '')),
                    'packaging_details'    => trim((string)($row[44] ?? '')),
                    'country_of_origin'    => trim((string)($row[45] ?? '')),
                    'main_image'           => trim((string)($row[46] ?? '')),
                    'additional_images'    => trim((string)($row[47] ?? '')),
                    'video_url'            => trim((string)($row[48] ?? '')),
                    'variants'             => [],
                    'rows'                 => [],
                    'errors'               => [],
                    'warnings'             => [],
                ];
            } else {
                // Forward fill product level attributes if subsequent row has non-empty fields
                if (empty($groupedProducts[$productSku]['name']) && !empty($productName)) {
                    $groupedProducts[$productSku]['name'] = $productName;
                }
                if (empty($groupedProducts[$productSku]['main_image']) && !empty($row[46])) {
                    $groupedProducts[$productSku]['main_image'] = trim((string)$row[46]);
                }
            }

            $pGroup = &$groupedProducts[$productSku];
            $pGroup['rows'][] = $rowIndex;

            // Variant Level Data
            $varType  = trim((string)($row[49] ?? ''));
            $varVal   = trim((string)($row[50] ?? ''));
            $varSku   = strtoupper(trim((string)($row[51] ?? '')));
            $varPrice = (float)($row[52] ?? 0);
            $varStock = (int)($row[53] ?? $pGroup['available_qty']);
            $varImg   = trim((string)($row[54] ?? ''));

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
                        ':video_url'      => $prod['video_url'],
                    ]);
                    $productId = (int)$this->db->lastInsertId();
                    $createdProducts++;
                }

                // Sync Wholesale Tiers into `tiered_prices`
                $this->syncWholesaleTiers($productId, $prod);

                // Sync All Metadata Specifications into `product_specifications`
                $this->syncProductSpecifications($productId, $prod);

                // Sync Filter Attributes into `product_filter_attribute_values`
                $this->syncProductFilterAttributes($productId, $prod);

                // Process Cover Main Image into product_images
                if (!empty($mainImgPath)) {
                    $chkM = $this->db->prepare("SELECT id FROM product_images WHERE product_id = ? AND (image_url = ? OR image_path = ?)");
                    $chkM->execute([$productId, $mainImgPath, $mainImgPath]);
                    if (!$chkM->fetch()) {
                        $stmtM = $this->db->prepare("INSERT INTO product_images (product_id, image_url, sort_order, is_primary) VALUES (?, ?, 0, 1)");
                        $stmtM->execute([$productId, $mainImgPath]);
                    }
                }

                // Process Additional Gallery Images
                if (!empty($prod['additional_images'])) {
                    $imgList = array_map('trim', explode(',', $prod['additional_images']));
                    foreach ($imgList as $gIdx => $gImgName) {
                        $gImgPath = $this->processImageSource($gImgName, $extractedZipDir);
                        if ($gImgPath && $gImgPath !== $mainImgPath) {
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
     * Save all metadata specs to product_specifications table.
     */
    private function syncProductSpecifications(int $productId, array $prod): void
    {
        $specMap = [
            'Jewellery Type'             => $prod['jewellery_type'] ?? '',
            'Gender'                     => $prod['gender'] ?? '',
            'Brand'                      => $prod['brand'] ?? '',
            'Material/Metal Type'        => $prod['material_type'] ?? '',
            'Metal Purity'               => $prod['metal_purity'] ?? '',
            'Metal Color'                => $prod['metal_color'] ?? '',
            'Plating Type'               => $prod['plating_type'] ?? '',
            'Main Stone Type'            => $prod['stone_type'] ?? '',
            'Stone Name'                 => $prod['stone_name'] ?? '',
            'Stone Color'                => $prod['stone_color'] ?? '',
            'Stone Shape'                => $prod['stone_shape'] ?? '',
            'Stone Weight/Carat'         => $prod['stone_carat'] ?? '',
            'Number of Stones'           => $prod['stone_count'] ?? '',
            'Gross Weight'               => $prod['gross_weight'] ?? '',
            'Net Weight'                 => $prod['net_weight'] ?? '',
            'Length'                     => $prod['length'] ?? '',
            'Width'                      => $prod['width'] ?? '',
            'Height/Thickness'           => $prod['height'] ?? '',
            'Ring Size'                  => $prod['ring_size'] ?? '',
            'Certification Available'    => !empty($prod['certification_available']) ? 'Yes' : 'No',
            'Certificate Type'           => $prod['certificate_type'] ?? '',
            'Currency'                   => $prod['currency'] ?? 'INR',
            'Price Negotiable'           => !empty($prod['price_negotiable']) ? 'Yes' : 'No',
            'OEM Available'              => !empty($prod['oem_available']) ? 'Yes' : 'No',
            'ODM Available'              => !empty($prod['odm_available']) ? 'Yes' : 'No',
            'Customization Available'    => !empty($prod['customization_available']) ? 'Yes' : 'No',
            'Sample Available'           => !empty($prod['sample_available']) ? 'Yes' : 'No',
            'Production Lead Time'       => $prod['production_lead_time'] ?? '',
            'Production Capacity'        => $prod['production_capacity'] ?? '',
            'Packaging Details'          => $prod['packaging_details'] ?? '',
            'Country of Origin'          => $prod['country_of_origin'] ?? '',
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
            $parsedRange = $this->parseQtyRange($prod['tier1_qty'], 2, 9);
            $tiers[] = [
                'min_qty'    => $parsedRange['min_qty'],
                'max_qty'    => $parsedRange['max_qty'],
                'unit_price' => $prod['tier1_price'],
            ];
        }

        if (!empty($prod['tier2_qty']) && $prod['tier2_price'] > 0) {
            $parsedRange = $this->parseQtyRange($prod['tier2_qty'], 10, 49);
            $tiers[] = [
                'min_qty'    => $parsedRange['min_qty'],
                'max_qty'    => $parsedRange['max_qty'],
                'unit_price' => $prod['tier2_price'],
            ];
        }

        if (!empty($prod['tier3_qty']) && $prod['tier3_price'] > 0) {
            $parsedRange = $this->parseQtyRange($prod['tier3_qty'], 50, null);
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
     * Download remote image URL.
     */
    private function downloadRemoteImage(string $url): ?string
    {
        try {
            $ctx = stream_context_create([
                'http' => ['timeout' => 5, 'user_agent' => 'ImportWala Importer/1.0']
            ]);
            $contents = @file_get_contents($url, false, $ctx);
            if ($contents) {
                $targetDir = ROOT_PATH . '/public/uploads/products/';
                if (!is_dir($targetDir)) {
                    mkdir($targetDir, 0755, true);
                }
                $ext = pathinfo(parse_url($url, PHP_URL_PATH), PATHINFO_EXTENSION) ?: 'jpg';
                $newFilename = 'url_' . time() . '_' . md5($url) . '.' . $ext;
                file_put_contents($targetDir . $newFilename, $contents);
                return 'uploads/products/' . $newFilename;
            }
        } catch (\Throwable $e) {}

        return $url; // Return raw URL fallback
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
        $subs = $stmt->fetchAll();
        $this->subcategoryCache = [];
        foreach ($subs as $s) {
            $this->subcategoryCache[$s['category_id'] . '_' . strtolower(trim($s['name']))] = (int)$s['id'];
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

    private function resolveOrCreateCategory(string $name): int
    {
        $name = trim($name);
        $cache = $this->getCategoryCache();
        $key = strtolower($name);
        if (isset($cache[$key])) return $cache[$key];

        $slug = $this->slugify($name);
        $stmt = $this->db->prepare("INSERT INTO categories (name, slug, status, created_at, updated_at) VALUES (?, ?, 'active', NOW(), NOW())");
        $stmt->execute([$name, $slug]);
        $newId = (int)$this->db->lastInsertId();
        $this->categoryCache[$key] = $newId;
        return $newId;
    }

    private function resolveOrCreateSubcategory(int $categoryId, string $name): int
    {
        $name = trim($name);
        $cache = $this->getSubcategoryCache();
        $key = $categoryId . '_' . strtolower($name);
        if (isset($cache[$key])) return $cache[$key];

        $slug = $this->slugify($name);
        $stmt = $this->db->prepare("INSERT INTO subcategories (category_id, name, slug, status, created_at, updated_at) VALUES (?, ?, ?, 'active', NOW(), NOW())");
        $stmt->execute([$categoryId, $name, $slug]);
        $newId = (int)$this->db->lastInsertId();
        $this->subcategoryCache[$key] = $newId;
        return $newId;
    }

    private function resolveOrCreateBrand(string $name): int
    {
        $name = trim($name);
        $cache = $this->getBrandCache();
        $key = strtolower($name);
        if (isset($cache[$key])) return $cache[$key];

        $slug = $this->slugify($name);
        $stmt = $this->db->prepare("INSERT INTO brands (name, slug, created_at, updated_at) VALUES (?, ?, NOW(), NOW())");
        $stmt->execute([$name, $slug]);
        $newId = (int)$this->db->lastInsertId();
        $this->brandCache[$key] = $newId;
        return $newId;
    }

    private function parseBool(mixed $val): int
    {
        $s = strtolower(trim((string)$val));
        return in_array($s, ['y', 'yes', 'true', '1']) ? 1 : 0;
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

    private function syncProductFilterAttributes(int $productId, array $prodData): void
    {
        try {
            $filterService = new FilterAttributeService();
            $attributes = $filterService->getAttributesForCategory($prodData['category_id'] ?? null);
            $attrDataToSave = [];

            foreach ($attributes as $attr) {
                $attrName = $attr['name'];
                $attrSlug = strtolower(str_replace([' ', '/', '-', '(', ')'], '_', $attrName));
                
                $val = $prodData[$attrSlug] ?? null;
                if ($val === null) {
                    // Try matching product data keys
                    foreach ($prodData as $k => $v) {
                        if (strcasecmp($k, $attrName) === 0 || strcasecmp(str_replace('_', ' ', $k), $attrName) === 0) {
                            $val = $v;
                            break;
                        }
                    }
                }

                if (!empty($val)) {
                    $valStr = trim((string)$val);
                    $optId = $filterService->getOrCreateOption($attr['id'], $valStr, true);
                    if ($optId) {
                        $attrDataToSave[$attr['id']] = [$optId];
                    }
                }
            }

            if (!empty($attrDataToSave)) {
                $filterService->saveProductAttributeValues($productId, $attrDataToSave);
            }
        } catch (\Throwable $e) {}
    }
}
