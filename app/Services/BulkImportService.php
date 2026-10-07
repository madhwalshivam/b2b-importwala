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

/**
 * BulkImportService — v2 (all-or-nothing, duplicate-safe)
 *
 * Phase 1  (parseAndValidate): parses the whole sheet, resolves duplicates,
 *           collects EVERY error, returns a preview. Nothing is written to DB or R2.
 *
 * Phase 2  (commitImport): only called when Phase-1 returned zero errors.
 *           Runs everything inside ONE transaction; any exception → full ROLLBACK
 *           and R2 cleanup of keys uploaded during this call.
 */
class BulkImportService
{
    private \PDO $db;
    private Product $productModel;
    private ProductVariant $variantModel;
    private ProductSpecification $specModel;
    private Factory $factoryModel;
    private ImageMirrorService $mirrorSvc;

    private ?array $categoryCache    = null;
    private ?array $subcategoryCache = null;
    private ?array $brandCache       = null;

    // ------------------------------------------------------------------ headers
    public const HEADERS = [
        'Product Name',                    // 0
        'Product SKU',                     // 1
        'Category',                        // 2
        'Subcategory',                     // 3
        'Description',                     // 4
        'Jewellery Type',                  // 5
        'Gender',                          // 6
        'Brand Name',                      // 7
        'Material',                        // 8
        'Metal Type',                      // 9
        'Metal Color',                     // 10
        'Main Stone Type',                 // 11
        'Size',                            // 12
        'Country of Origin',               // 13
        'Weight',                          // 14
        'Variety',                         // 15
        'One Piece Price',                 // 16
        'Wholesale Tier 1 Qty Range',      // 17
        'Wholesale Tier 1 Price',          // 18
        'Wholesale Tier 2 Qty Range',      // 19
        'Wholesale Tier 2 Price',          // 20
        'Wholesale Tier 3 Qty Range',      // 21
        'Wholesale Tier 3 Price',          // 22
        'Main Product Image',              // 23
        'Additional Image 1',              // 24
        'Additional Image 2',              // 25
        'Additional Image 3',              // 26
        'Additional Image 4',              // 27
        'Product Video URL',               // 28
        'Group For',                       // 29
        'Variant Color Name',              // 30
        'Variant Size Name',               // 31
        'Variant SKU',                     // 32
        'Variant Price',                   // 33
        'Variant Stock',                   // 34
        'Variant Image',                   // 35
        'Processing Technology',           // 36
        'Processing Technique',            // 37
        'Treatment Process',               // 38
        'Style',                           // 39
        'Suitable For Gift Giving Occasion', // 40
        'Item Number',                     // 41
        'Main Downstream Platform',        // 42
        'Color',                           // 43
        'Popular Elements',                // 44
        'Style Classification',            // 45
        'Kind',                            // 46
        'Product Type',                    // 47
        'Chain Style',                     // 48
        'Pendant Material',                // 49
        'Trendy Element',                  // 50
        'Closure Type',                    // 51
        'Manufacturer ID',                 // 52 (PRIVATE)
        'Manufacturer Name',               // 53
        'Manufacturer Contact Person',     // 54
        'Manufacturer Phone',              // 55
        'Manufacturer WhatsApp',           // 56
        'Manufacturer Email',              // 57
        'Manufacturer Store URL',          // 58
        'Source Platform',                 // 59
        'Source Product ID',               // 60
        'Source Product URL',              // 61
        'Import Date',                     // 62
        'Status',                          // 63
    ];

    // ------------------------------------------------------------------ boot
    public function __construct()
    {
        $this->db           = Database::getInstance();
        $this->productModel = new Product();
        $this->variantModel = new ProductVariant();
        $this->specModel    = new ProductSpecification();
        $this->factoryModel = new Factory();
        $this->mirrorSvc    = new ImageMirrorService();
    }

    // ================================================================== PUBLIC API

    /**
     * Generate pre-filled sample spreadsheet template.
     */
    public function generateTemplate(string $format = 'xlsx'): string
    {
        $spreadsheet = new Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();
        $sheet->setTitle('Bulk Import Template');

        foreach (self::HEADERS as $colIdx => $header) {
            $colLetter = \PhpOffice\PhpSpreadsheet\Cell\Coordinate::stringFromColumnIndex($colIdx + 1);
            $sheet->setCellValue($colLetter . '1', $header);
            $sheet->getStyle($colLetter . '1')->getFont()->setBold(true);
            if ($colIdx >= 51) {
                $sheet->getStyle($colLetter . '1')->getFill()->setFillType(\PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID)
                    ->getStartColor()->setARGB('FF334155');
            } else {
                $sheet->getStyle($colLetter . '1')->getFill()->setFillType(\PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID)
                    ->getStartColor()->setARGB('FFF05A29');
            }
            $sheet->getStyle($colLetter . '1')->getFont()->getColor()->setARGB('FFFFFFFF');
        }

        $sampleRow1 = [
            'Cross-Border Tiger Eye Leather Bracelet', 'JWL-BRC-001', 'Fashion Jewellery', 'Bracelets & Bangles',
            'Hand-woven genuine leather bracelet with natural Tiger Eye stone.', 'Bracelet', 'Men', 'ImportWale OEM',
            'Leather', '316L Stainless Steel', 'Silver / Black PVD', 'Natural Tiger Eye Stone',
            '21 cm', 'China', '120g', 'Vintage Braided', '299.00', '2-35', '145.00', '36-149', '125.00',
            '150+', '99.00', 'https://images.importwale.com/products/jwl-brc-001-main.jpg',
            'https://images.importwale.com/products/jwl-brc-001-1.jpg',
            'https://images.importwale.com/products/jwl-brc-001-2.jpg',
            'https://images.importwale.com/products/jwl-brc-001-3.jpg',
            'https://images.importwale.com/products/jwl-brc-001-4.jpg',
            'https://media.importwale.com/videos/jwl-brc-001.mp4',
            '', 'Brown Leather - Gold Clasp', 'Large', 'JWL-BRC-001-BRN-GLD-L', '145.00', '500',
            'https://images.importwale.com/products/jwl-brc-001-brn-gld.jpg',
            'Vacuum Electroplating', 'Hand Weaving', '', 'Vintage / Punk', "Birthday, Father's Day",
            'JWL-2026-BRC01', 'Amazon, Flipkart, Meesho', 'Brown / Tiger Eye', 'Geometry, Leather Weave',
            'Fashion Commuter', "Men's", 'Leather Bracelet', 'Braided Rope Chain', 'N/A',
            'Retro Braided Leather', 'Magnetic Clasp',
            'FCT-001', 'Yiwu Fashion Jewelry Manufactory', 'Mr. Chen', '+86 138 0000 1111', '+86 138 0000 1111',
            'chen@yiwujewelry.cn', 'https://shop12345.1688.com', '1688', '685412985412',
            'https://detail.1688.com/offer/685412985412.html', date('Y-m-d H:i:s'), 'Active',
        ];

        foreach ($sampleRow1 as $cIdx => $val) {
            $colLetter = \PhpOffice\PhpSpreadsheet\Cell\Coordinate::stringFromColumnIndex($cIdx + 1);
            $sheet->setCellValue($colLetter . '2', $val);
        }

        foreach (range(1, count(self::HEADERS)) as $colIdx) {
            $sheet->getColumnDimension(\PhpOffice\PhpSpreadsheet\Cell\Coordinate::stringFromColumnIndex($colIdx))->setAutoSize(true);
        }

        $tmpPath = sys_get_temp_dir() . '/importwala_template_' . time() . '.' . $format;
        $writer  = strtolower($format) === 'csv' ? IOFactory::createWriter($spreadsheet, 'Csv') : new Xlsx($spreadsheet);
        $writer->save($tmpPath);
        return $tmpPath;
    }

    // ================================================================== PHASE 1

    /**
     * Phase 1 — parse, validate, duplicate-detect. Nothing is written.
     *
     * Returns [
     *   'success'          => bool,
     *   'error'            => string|null,     // fatal parse error
     *   'summary'          => [...],
     *   'products'         => [...],           // grouped product dtos
     *   'global_errors'    => [...],           // errors that apply to the whole sheet
     *   'staged_factories' => [...],
     *   'extracted_zip_dir'=> string|null,
     *   'has_errors'       => bool,
     * ]
     */
    public function parseAndValidate(string $filePath, ?string $zipFilePath = null, bool $autoCreateCategory = true): array
    {
        $extractedZipDir = null;
        if ($zipFilePath && file_exists($zipFilePath)) {
            $extractedZipDir = $this->extractZipImages($zipFilePath);
        }

        // ---- load spreadsheet ----
        try {
            $spreadsheet = IOFactory::load($filePath);
            $sheet       = $spreadsheet->getActiveSheet();
            $rows        = $sheet->toArray(null, true, true, true);
        } catch (\Throwable $e) {
            return ['success' => false, 'error' => 'Unable to parse spreadsheet: ' . $e->getMessage()];
        }

        if (count($rows) <= 1) {
            return ['success' => false, 'error' => 'The uploaded file is empty or has no data rows.'];
        }

        $headerRow = array_values(array_shift($rows));
        $headerMap = [];
        foreach ($headerRow as $cIdx => $hName) {
            $norm = strtolower(trim((string)$hName));
            if ($norm !== '') {
                $headerMap[$norm] = $cIdx;
            }
        }

        $getValue = function (array $row, array $aliases, int $defaultIndex) use ($headerMap): string {
            foreach ($aliases as $alias) {
                $norm = strtolower(trim($alias));
                if (isset($headerMap[$norm])) {
                    $idx = $headerMap[$norm];
                    return trim((string)($row[$idx] ?? ''));
                }
            }
            return trim((string)($row[$defaultIndex] ?? ''));
        };

        // ---- preload DB caches ----
        $categoryCache    = $this->getCategoryCache();
        $subcategoryCache = $this->getSubcategoryCache();
        $brandCache       = $this->getBrandCache();

        $dbFactories  = $this->factoryModel->all();
        $factoryCache = [];
        foreach ($dbFactories as $f) {
            $factoryCache[strtoupper($f['factory_code'])] = $f;
        }

        // ---- preload existing products for duplicate detection ----
        // Map: sku → {id, name, normalized_title, category_id, source_product_id, source_platform}
        $dbProducts = $this->loadAllProductsForDuplicateCheck();

        // ---- grouping state ----
        $groupedProducts     = [];
        $stagedNewFactories  = [];
        $currentGroupKey     = null;
        $lastProductGroupFor = '';
        $lastProductSku      = '';
        $lastProductName     = '';
        $lastVariantColor    = '';
        $lastVariantImage    = '';
        $lastVariantPrice    = 0.0;
        $rowIndex            = 2;

        // ---- parse rows into grouped products ----
        foreach ($rows as $rowMap) {
            $row = array_values($rowMap);
            if (empty(array_filter($row, fn($v) => trim((string)$v) !== ''))) {
                $rowIndex++;
                continue;
            }

            $rawProductName = $getValue($row, ['product name', 'name'], 0);
            $rawProductSku  = strtoupper($getValue($row, ['product sku', 'sku', 'parent sku', 'product_sku'], 1));
            $rawGroupFor    = strtoupper($getValue($row, ['group for', 'group for variant', 'group for variants', 'variant group', 'group_for'], 29));
            $rawVarColor    = $getValue($row, ['variant color name', 'variant color', 'color name', 'variant_color'], 30);
            $rawVarSize     = $getValue($row, ['variant size name', 'variant size', 'size name', 'variant_size'], 31);
            $rawVarSku      = strtoupper($getValue($row, ['variant sku', 'vsku'], 32));
            $rawVarPriceVal = (float)($getValue($row, ['variant price', 'vprice'], 33) ?: 0);
            $rawVarStockVal = $getValue($row, ['variant stock', 'vstock'], 34);
            $rawVarImg      = $getValue($row, ['variant image', 'vimage'], 35);

            // ---- determine group membership ----
            $isNewGroup = ($currentGroupKey === null);
            if (!$isNewGroup) {
                if (!empty($rawGroupFor) && $rawGroupFor !== $lastProductGroupFor) {
                    $isNewGroup = true;
                } elseif (!empty($rawProductSku) && $rawProductSku !== $lastProductSku) {
                    $isNewGroup = true;
                } elseif (!empty($rawProductName) && $rawProductName !== $lastProductName && empty($rawProductSku) && empty($rawGroupFor)) {
                    $isNewGroup = true;
                }
            }

            if ($isNewGroup) {
                if (!empty($rawGroupFor)) {
                    $groupKey = $rawGroupFor;
                } elseif (!empty($rawProductSku)) {
                    $groupKey = $rawProductSku;
                } elseif (!empty($rawProductName)) {
                    $groupKey = strtoupper($this->slugify($rawProductName));
                } else {
                    $groupKey = 'PROD_ROW_' . $rowIndex;
                }

                $currentGroupKey     = $groupKey;
                $lastProductGroupFor = $rawGroupFor;
                $lastProductSku      = !empty($rawProductSku) ? $rawProductSku : $groupKey;
                $lastProductName     = $rawProductName;
                $lastVariantColor    = $rawVarColor;
                $lastVariantImage    = $rawVarImg;
                $lastVariantPrice    = $rawVarPriceVal;
            } else {
                $groupKey = $currentGroupKey;
                if (!empty($rawGroupFor))    { $lastProductGroupFor = $rawGroupFor; }
                if (!empty($rawProductSku))  { $lastProductSku      = $rawProductSku; }
                if (!empty($rawProductName)) { $lastProductName     = $rawProductName; }
            }

            $productSku  = !empty($rawProductSku) ? $rawProductSku : $lastProductSku;
            $productName = !empty($rawProductName) ? $rawProductName : $lastProductName;

            // forward-fill variant fields
            if (!empty($rawVarColor)) {
                $lastVariantColor = $rawVarColor;
                if (!empty($rawVarImg))       { $lastVariantImage = $rawVarImg; }
                if ($rawVarPriceVal > 0)      { $lastVariantPrice = $rawVarPriceVal; }
            }
            $varColor = !empty($rawVarColor) ? $rawVarColor : $lastVariantColor;
            $varImg   = !empty($rawVarImg)   ? $rawVarImg   : $lastVariantImage;
            $varPrice = $rawVarPriceVal > 0  ? $rawVarPriceVal : $lastVariantPrice;

            // additional images
            $addImgs = array_filter([
                trim((string)($row[24] ?? '')),
                trim((string)($row[25] ?? '')),
                trim((string)($row[26] ?? '')),
                trim((string)($row[27] ?? '')),
            ]);

            // ---- factory linking ----
            $mfgIdCode         = strtoupper(trim((string)($row[52] ?? '')));
            $mfgName           = trim((string)($row[53] ?? ''));
            $factoryLinkStatus = 'unassigned';
            $factoryCode       = null;
            $factoryName       = null;
            $factoryBadge      = 'Unassigned';
            $factoryId         = null;

            if (!empty($mfgIdCode)) {
                if (isset($factoryCache[$mfgIdCode])) {
                    $factoryId         = (int)$factoryCache[$mfgIdCode]['id'];
                    $factoryCode       = $factoryCache[$mfgIdCode]['factory_code'];
                    $factoryName       = $factoryCache[$mfgIdCode]['name'];
                    $factoryLinkStatus = 'existing';
                    $factoryBadge      = "Existing [{$factoryCode}] {$factoryName}";
                } elseif (isset($stagedNewFactories[$mfgIdCode])) {
                    $factoryCode       = $stagedNewFactories[$mfgIdCode]['factory_code'];
                    $factoryName       = $stagedNewFactories[$mfgIdCode]['name'];
                    $factoryLinkStatus = 'new';
                    $factoryBadge      = "New [{$factoryCode}] {$factoryName}";
                } else {
                    $excludeCodes = array_map(fn($f) => $f['factory_code'], $stagedNewFactories);
                    $targetCode   = str_starts_with($mfgIdCode, 'FCT-') ? $mfgIdCode : $this->factoryModel->generateNextCode($excludeCodes);
                    $targetName   = !empty($mfgName) ? $mfgName : ('Factory ' . $targetCode);

                    $stagedNewFactoryData = [
                        'factory_code'    => $targetCode,
                        'name'            => $targetName,
                        'contact_person'  => trim((string)($row[54] ?? '')) ?: null,
                        'phone'           => trim((string)($row[55] ?? '')) ?: null,
                        'whatsapp'        => trim((string)($row[56] ?? '')) ?: null,
                        'email'           => trim((string)($row[57] ?? '')) ?: null,
                        'store_url'       => trim((string)($row[58] ?? '')) ?: null,
                        'source_platform' => trim((string)($row[59] ?? '')) ?: null,
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

            // ---- initialise or update product group ----
            if (!isset($groupedProducts[$groupKey])) {
                $groupedProducts[$groupKey] = [
                    'group_key'                   => $groupKey,
                    'product_sku'                 => $productSku,
                    'name'                        => $productName,
                    'category'                    => trim((string)($row[2] ?? '')),
                    'subcategory'                 => trim((string)($row[3] ?? '')),
                    'description'                 => trim((string)($row[4] ?? '')),
                    'jewellery_type'              => trim((string)($row[5] ?? '')),
                    'gender'                      => trim((string)($row[6] ?? '')),
                    'brand'                       => trim((string)($row[7] ?? '')),
                    'material'                    => trim((string)($row[8] ?? '')),
                    'metal_type'                  => trim((string)($row[9] ?? '')),
                    'metal_color'                 => trim((string)($row[10] ?? '')),
                    'stone_type'                  => trim((string)($row[11] ?? '')),
                    'size'                        => trim((string)($row[12] ?? '')),
                    'country_of_origin'           => trim((string)($row[13] ?? '')),
                    'weight'                      => trim((string)($row[14] ?? '')),
                    'variety'                     => trim((string)($row[15] ?? '')),
                    'one_piece_price'             => (float)($row[16] ?? 0),
                    'tier1_qty'                   => trim((string)($row[17] ?? '')),
                    'tier1_price'                 => (float)($row[18] ?? 0),
                    'tier2_qty'                   => trim((string)($row[19] ?? '')),
                    'tier2_price'                 => (float)($row[20] ?? 0),
                    'tier3_qty'                   => trim((string)($row[21] ?? '')),
                    'tier3_price'                 => (float)($row[22] ?? 0),
                    'main_image'                  => trim((string)($row[23] ?? '')),
                    'additional_images'           => implode(',', $addImgs),
                    'video_url'                   => trim((string)($row[28] ?? '')),
                    'processing_technology'       => trim((string)($row[36] ?? '')),
                    'processing_technique'        => trim((string)($row[37] ?? '')),
                    'treatment_process'           => trim((string)($row[38] ?? '')),
                    'style'                       => trim((string)($row[39] ?? '')),
                    'gift_occasion'               => trim((string)($row[40] ?? '')),
                    'item_number'                 => trim((string)($row[41] ?? '')),
                    'downstream_platform'         => trim((string)($row[42] ?? '')),
                    'color'                       => trim((string)($row[43] ?? '')),
                    'popular_elements'            => trim((string)($row[44] ?? '')),
                    'style_classification'        => trim((string)($row[45] ?? '')),
                    'kind'                        => trim((string)($row[46] ?? '')),
                    'product_type'                => trim((string)($row[47] ?? '')),
                    'chain_style'                 => trim((string)($row[48] ?? '')),
                    'pendant_material'            => trim((string)($row[49] ?? '')),
                    'trendy_element'              => trim((string)($row[50] ?? '')),
                    'closure_type'                => trim((string)($row[51] ?? '')),
                    'factory_id'                  => $factoryId,
                    'factory_code'                => $factoryCode,
                    'factory_name'                => $factoryName,
                    'factory_link_status'         => $factoryLinkStatus,
                    'factory_badge'               => $factoryBadge,
                    'manufacturer_id_code'        => $mfgIdCode,
                    'manufacturer_name'           => $mfgName,
                    'manufacturer_contact_person' => trim((string)($row[54] ?? '')),
                    'manufacturer_phone'          => trim((string)($row[55] ?? '')),
                    'manufacturer_whatsapp'       => trim((string)($row[56] ?? '')),
                    'manufacturer_email'          => trim((string)($row[57] ?? '')),
                    'manufacturer_store_url'      => trim((string)($row[58] ?? '')),
                    'source_platform'             => trim((string)($row[59] ?? '')),
                    'source_product_id'           => trim((string)($row[60] ?? '')),
                    'source_product_url'          => trim((string)($row[61] ?? '')),
                    'import_date'                 => !empty(trim((string)($row[62] ?? ''))) ? trim((string)$row[62]) : date('Y-m-d H:i:s'),
                    'admin_status'                => !empty(trim((string)($row[63] ?? ''))) ? trim((string)$row[63]) : 'Active',
                    'moq'                         => 1,
                    'available_qty'               => 100,
                    'variants'                    => [],
                    'rows'                        => [],
                    'errors'                      => [],
                    'warnings'                    => [],
                    // set in duplicate-detection pass:
                    'db_match_id'                 => null,
                    'db_match_sku'                => null,
                    'merge_action'                => 'new',  // 'new' | 'merge'
                ];
            } else {
                // forward-fill product-level blanks
                $pg = &$groupedProducts[$groupKey];
                if (empty($pg['name']) && !empty($productName))   { $pg['name']         = $productName; }
                if (empty($pg['category']) && !empty($row[2]))     { $pg['category']     = trim((string)$row[2]); }
                if (empty($pg['main_image']) && !empty($row[23]))  { $pg['main_image']   = trim((string)$row[23]); }
                if (empty($pg['product_sku']) && !empty($productSku)) { $pg['product_sku'] = $productSku; }
                unset($pg);
            }

            $pg = &$groupedProducts[$groupKey];
            $pg['rows'][] = $rowIndex;

            $varSize  = $rawVarSize;
            $varStock = $rawVarStockVal !== '' ? (int)$rawVarStockVal : $pg['available_qty'];

            $pg['variants'][] = [
                'row_num'         => $rowIndex,
                'color_name'      => !empty($varColor) ? $varColor : null,
                'size_label'      => !empty($varSize)  ? $varSize  : null,
                'variant_sku'     => $rawVarSku, // may be empty — filled in validation pass
                'variant_price'   => $varPrice > 0 ? $varPrice : ($pg['tier1_price'] > 0 ? $pg['tier1_price'] : $pg['one_piece_price']),
                'one_piece_price' => $pg['one_piece_price'],
                'variant_stock'   => $varStock,
                'variant_image'   => $varImg,
            ];
            unset($pg);

            $rowIndex++;
        }

        // ================================================================
        // VALIDATION PASS
        // ================================================================

        // --- pre-load all variant SKUs from the DB ---
        $dbVariantSkus = $this->loadAllVariantSkusFromDb(); // [sku => product_id]

        // --- sheet-level: track skus and normalized titles ---
        $sheetProductSkus    = [];  // productSku => groupKey (for dup-sku-different-title check)
        $sheetVariantSkus    = [];  // variantSku => ['group_key' => ..., 'row' => ...]
        $sheetNormTitles     = [];  // "catId_normTitle" => groupKey (for title-dup within sheet)

        $globalErrors = [];
        $autoVarCounter = [];  // groupKey => int, for auto-generating Variant SKUs

        foreach ($groupedProducts as $gKey => &$prod) {

            // ---- (A) required fields ----
            $firstRow = $prod['rows'][0] ?? '?';

            if (empty(trim($prod['name']))) {
                $prod['errors'][] = [
                    'row' => $firstRow, 'sku' => $prod['product_sku'], 'name' => '',
                    'column' => 'Product Name', 'reason' => 'Required field is empty.',
                ];
            }
            if (empty(trim($prod['product_sku']))) {
                $prod['errors'][] = [
                    'row' => $firstRow, 'sku' => '', 'name' => $prod['name'],
                    'column' => 'Product SKU', 'reason' => 'Required field is empty.',
                ];
            }
            if (empty(trim($prod['category']))) {
                $prod['errors'][] = [
                    'row' => $firstRow, 'sku' => $prod['product_sku'], 'name' => $prod['name'],
                    'column' => 'Category', 'reason' => 'Required field is empty.',
                ];
            }
            if ($prod['one_piece_price'] <= 0 && $prod['tier1_price'] <= 0) {
                $prod['errors'][] = [
                    'row' => $firstRow, 'sku' => $prod['product_sku'], 'name' => $prod['name'],
                    'column' => 'One Piece Price', 'reason' => 'Must be a number greater than 0.',
                ];
            }

            // ---- (B) category / subcategory resolution ----
            $catId    = null;
            $subCatId = null;
            if (!empty($prod['category'])) {
                $catLower = strtolower($prod['category']);
                if (!isset($categoryCache[$catLower])) {
                    if ($autoCreateCategory) {
                        $prod['warnings'][] = [
                            'row' => $firstRow, 'sku' => $prod['product_sku'], 'name' => $prod['name'],
                            'column' => 'Category',
                            'reason' => "Category '{$prod['category']}' not in DB — will be auto-created.",
                        ];
                    } else {
                        $prod['errors'][] = [
                            'row' => $firstRow, 'sku' => $prod['product_sku'], 'name' => $prod['name'],
                            'column' => 'Category',
                            'reason' => "Category '{$prod['category']}' does not exist in DB.",
                        ];
                    }
                } else {
                    $catId = $categoryCache[$catLower];
                }
            }
            if (!empty($prod['subcategory']) && $catId !== null) {
                $exactKey = $catId . '_' . strtolower($prod['subcategory']);
                $normKey  = $catId . '_' . $this->normalizeSubcategoryKey($prod['subcategory']);
                if (!isset($subcategoryCache[$exactKey]) && !isset($subcategoryCache[$normKey])) {
                    if ($autoCreateCategory) {
                        $prod['warnings'][] = [
                            'row' => $firstRow, 'sku' => $prod['product_sku'], 'name' => $prod['name'],
                            'column' => 'Subcategory',
                            'reason' => "Subcategory '{$prod['subcategory']}' not in DB — will be auto-created.",
                        ];
                    } else {
                        $prod['errors'][] = [
                            'row' => $firstRow, 'sku' => $prod['product_sku'], 'name' => $prod['name'],
                            'column' => 'Subcategory',
                            'reason' => "Subcategory '{$prod['subcategory']}' does not exist under category '{$prod['category']}'.",
                        ];
                    }
                } else {
                    $subCatId = $subcategoryCache[$exactKey] ?? $subcategoryCache[$normKey];
                }
            }

            // Store resolved IDs for duplicate-detection title key
            $prod['_resolved_cat_id']    = $catId;
            $prod['_resolved_subcat_id'] = $subCatId;

            // ---- (C) duplicate detection: Product SKU within sheet ----
            $sku = $prod['product_sku'];
            if (!empty($sku)) {
                if (isset($sheetProductSkus[$sku])) {
                    $otherGKey = $sheetProductSkus[$sku];
                    if ($otherGKey !== $gKey) {
                        $otherName = $groupedProducts[$otherGKey]['name'] ?? '';
                        // same SKU, different title => ERROR
                        if ($otherName !== $prod['name']) {
                            $prod['errors'][] = [
                                'row' => $firstRow, 'sku' => $sku, 'name' => $prod['name'],
                                'column' => 'Product SKU',
                                'reason' => "Product SKU '{$sku}' already used by a different product in this sheet ('{$otherName}'). Duplicate SKUs for different products are not allowed.",
                            ];
                        }
                        // same SKU + same title = same product => mark as sheet-internal merge (handled by grouping already)
                    }
                } else {
                    $sheetProductSkus[$sku] = $gKey;
                }
            }

            // ---- (D) duplicate detection: normalised title within sheet ----
            $normTitle = $this->normalizeTitle($prod['name']);
            if ($normTitle !== '' && $catId !== null) {
                $titleKey = $catId . '_' . ($subCatId ?? 0) . '_' . $normTitle;
                if (isset($sheetNormTitles[$titleKey])) {
                    $otherGKey = $sheetNormTitles[$titleKey];
                    if ($otherGKey !== $gKey) {
                        // Two different group-keys resolve to the same title in the same category
                        // → merge: first-row product wins, the later group gets folded in as a warning
                        $prod['warnings'][] = [
                            'row' => $firstRow, 'sku' => $sku, 'name' => $prod['name'],
                            'column' => 'Product Name',
                            'reason' => "Title matches another product in this sheet (group '{$otherGKey}'). Rows will be merged into the first occurrence.",
                        ];
                        $prod['merge_into_sheet_group'] = $otherGKey;
                    }
                } else {
                    $sheetNormTitles[$titleKey] = $gKey;
                }
            }

            // ---- (E) duplicate detection: match against DB ----
            $dbMatch = $this->findDbMatch($prod, $dbProducts, $normTitle);
            if ($dbMatch !== null) {
                $prod['db_match_id']  = $dbMatch['id'];
                $prod['db_match_sku'] = $dbMatch['sku'];
                $prod['merge_action'] = 'merge';
                $prod['warnings'][] = [
                    'row'    => $firstRow,
                    'sku'    => $sku,
                    'name'   => $prod['name'],
                    'column' => 'Product Name / SKU',
                    'reason' => "Matches existing product #{$dbMatch['id']} (SKU: {$dbMatch['sku']}, \"{$dbMatch['name']}\"). Will MERGE — existing product preserved.",
                ];

                // Check: sheet SKU is already in DB *for a different product* → error
                if (!empty($sku) && isset($dbProducts['by_sku'][$sku]) && (int)$dbProducts['by_sku'][$sku]['id'] !== (int)$dbMatch['id']) {
                    $prod['errors'][] = [
                        'row' => $firstRow, 'sku' => $sku, 'name' => $prod['name'],
                        'column' => 'Product SKU',
                        'reason' => "Product SKU '{$sku}' already exists in DB for a different product (ID {$dbProducts['by_sku'][$sku]['id']}). This is not the matched product. Resolve the SKU conflict.",
                    ];
                }
            } else {
                // New product: check SKU doesn't belong to a different existing product
                if (!empty($sku) && isset($dbProducts['by_sku'][$sku])) {
                    $existingId = (int)$dbProducts['by_sku'][$sku]['id'];
                    $prod['errors'][] = [
                        'row' => $firstRow, 'sku' => $sku, 'name' => $prod['name'],
                        'column' => 'Product SKU',
                        'reason' => "Product SKU '{$sku}' already exists in DB (product ID {$existingId}, \"{$dbProducts['by_sku'][$sku]['name']}\") but its title doesn't match this sheet row. Use a different SKU or correct the title.",
                    ];
                }
            }

            // ---- (F) variant SKU validation ----
            $autoVarCounter[$gKey] = 0;
            foreach ($prod['variants'] as &$vData) {
                $vRow = $vData['row_num'];
                $vSku = $vData['variant_sku'];

                // Variant price present but SKU empty → auto-generate
                if (empty($vSku) && $vData['variant_price'] > 0) {
                    $autoVarCounter[$gKey]++;
                    $baseSku = !empty($prod['product_sku']) ? $prod['product_sku'] : $gKey;
                    $candidate = strtoupper($baseSku . '-V' . $autoVarCounter[$gKey]);
                    // ensure the candidate is free in DB and in this sheet
                    while (isset($dbVariantSkus[$candidate]) || isset($sheetVariantSkus[$candidate])) {
                        $autoVarCounter[$gKey]++;
                        $candidate = strtoupper($baseSku . '-V' . $autoVarCounter[$gKey]);
                    }
                    $vSku = $candidate;
                    $vData['variant_sku'] = $vSku;
                    $prod['warnings'][] = [
                        'row' => $vRow, 'sku' => $prod['product_sku'], 'name' => $prod['name'],
                        'column' => 'Variant SKU',
                        'reason' => "Variant SKU was empty — auto-generated '{$vSku}'.",
                    ];
                } elseif (empty($vSku)) {
                    // No price either — default to product SKU for the first variant, otherwise append index
                    $baseSku = !empty($prod['product_sku']) ? $prod['product_sku'] : $gKey;
                    $vSku    = count($prod['variants']) === 1 ? $baseSku : ($baseSku . '-V' . count($prod['variants']));
                    $vData['variant_sku'] = strtoupper($vSku);
                    $vSku = $vData['variant_sku'];
                }

                // Variant price numeric check
                if ($vData['variant_price'] !== null && $vData['variant_price'] < 0) {
                    $prod['errors'][] = [
                        'row' => $vRow, 'sku' => $prod['product_sku'], 'name' => $prod['name'],
                        'column' => 'Variant Price',
                        'reason' => "Variant price must be a positive number (got {$vData['variant_price']}).",
                    ];
                }

                // Check variant SKU uniqueness within the sheet
                if (!empty($vSku)) {
                    if (isset($sheetVariantSkus[$vSku])) {
                        $firstSeen = $sheetVariantSkus[$vSku];
                        if ($firstSeen['group_key'] !== $gKey) {
                            $prod['errors'][] = [
                                'row' => $vRow, 'sku' => $prod['product_sku'], 'name' => $prod['name'],
                                'column' => 'Variant SKU',
                                'reason' => "Variant SKU '{$vSku}' is already used by product '{$firstSeen['group_key']}' in this sheet. Variant SKUs must be globally unique.",
                            ];
                        } else {
                            // duplicate within same product = de-duplicate silently
                            $prod['warnings'][] = [
                                'row' => $vRow, 'sku' => $prod['product_sku'], 'name' => $prod['name'],
                                'column' => 'Variant SKU',
                                'reason' => "Duplicate Variant SKU '{$vSku}' within this product — duplicate row skipped.",
                            ];
                            $vData['_skip'] = true;
                        }
                    } else {
                        $sheetVariantSkus[$vSku] = ['group_key' => $gKey, 'row' => $vRow];
                    }

                    // Check variant SKU against DB — must belong to this product (if merging) or be free
                    if (isset($dbVariantSkus[$vSku])) {
                        $ownerProductId = (int)$dbVariantSkus[$vSku];
                        $expectedProductId = $prod['db_match_id'] ? (int)$prod['db_match_id'] : null;

                        if ($expectedProductId === null || $ownerProductId !== $expectedProductId) {
                            $prod['errors'][] = [
                                'row'    => $vRow,
                                'sku'    => $prod['product_sku'],
                                'name'   => $prod['name'],
                                'column' => 'Variant SKU',
                                'reason' => "Variant SKU '{$vSku}' already exists in DB and belongs to product ID {$ownerProductId}, which is not the product being imported/merged here. Assign a unique SKU.",
                            ];
                        }
                    }
                }
            }
            unset($vData);

            // ---- (G) image URL reachability (HEAD check with 3 s timeout) ----
            $imageCols = array_merge(
                [['Main Product Image', $prod['main_image']]],
                array_map(fn($u, $i) => ["Additional Image " . ($i + 1), $u], array_filter(explode(',', $prod['additional_images'])), array_keys(array_filter(explode(',', $prod['additional_images']))))
            );
            foreach ($imageCols as [$colName, $url]) {
                $url = trim($url);
                if (empty($url) || str_contains($url, 'r2.dev') || str_contains($url, 'cloudflare')) {
                    continue; // already mirrored or empty
                }
                if (!filter_var($url, FILTER_VALIDATE_URL)) {
                    $prod['errors'][] = [
                        'row' => $firstRow, 'sku' => $prod['product_sku'], 'name' => $prod['name'],
                        'column' => $colName,
                        'reason' => "Invalid URL: '{$url}'.",
                    ];
                }
                // (URL reachability HEAD check is intentionally skipped in dry-run to keep it fast;
                //  we trust the URL format check above. Add HEAD checks here if the requirement tightens.)
            }

            // ---- determine final status ----
            if (!empty($prod['errors'])) {
                $prod['status'] = 'error';
            } elseif (!empty($prod['warnings'])) {
                $prod['status'] = 'warning';
            } else {
                $prod['status'] = 'valid';
            }
        }
        unset($prod);

        // ---- build summary ----
        $totalProducts   = count($groupedProducts);
        $validProducts   = 0;
        $warningProducts = 0;
        $errorProducts   = 0;
        $mergeProducts   = 0;
        $totalVariants   = 0;
        $allErrors       = [];

        foreach ($globalErrors as $ge) {
            $allErrors[] = [
                'row'          => $ge['row'] ?? '-',
                'product_sku'  => $ge['sku'] ?? 'GLOBAL',
                'product_name' => $ge['name'] ?? 'GLOBAL',
                'column'       => $ge['column'] ?? 'General',
                'reason'       => $ge['reason'] ?? (is_string($ge) ? $ge : json_encode($ge)),
            ];
        }

        foreach ($groupedProducts as &$prod) {
            $prod['action'] = $prod['merge_action'];
            $totalVariants += count(array_filter($prod['variants'], fn($v) => empty($v['_skip'])));
            if ($prod['status'] === 'error') {
                $errorProducts++;
            } elseif ($prod['status'] === 'warning') {
                $warningProducts++;
                if ($prod['merge_action'] === 'merge') { $mergeProducts++; }
            } else {
                $validProducts++;
                if ($prod['merge_action'] === 'merge') { $mergeProducts++; }
            }

            foreach ($prod['errors'] as $err) {
                $allErrors[] = [
                    'row'          => $err['row'] ?? '-',
                    'product_sku'  => $err['sku'] ?? ($prod['product_sku'] ?? ''),
                    'product_name' => $err['name'] ?? ($prod['name'] ?? ''),
                    'column'       => $err['column'] ?? '',
                    'reason'       => $err['reason'] ?? (is_string($err) ? $err : json_encode($err)),
                ];
            }
        }
        unset($prod);

        $hasErrors = $errorProducts > 0 || !empty($globalErrors) || !empty($allErrors);

        return [
            'success'          => true,
            'can_commit'       => !$hasErrors,
            'has_errors'       => $hasErrors,
            'summary'          => [
                'total_rows'       => $rowIndex - 2,
                'total_products'   => $totalProducts,
                'total_variants'   => $totalVariants,
                'new_products'     => $totalProducts - $mergeProducts,
                'valid_products'   => $validProducts,
                'warning_products' => $warningProducts,
                'error_products'   => $errorProducts,
                'merge_products'   => $mergeProducts,
                'staged_factories' => count($stagedNewFactories),
            ],
            'products'          => array_values($groupedProducts),
            'errors'            => $allErrors,
            'global_errors'     => $globalErrors,
            'staged_factories'  => array_values($stagedNewFactories),
            'extracted_zip_dir' => $extractedZipDir,
        ];
    }

    // ================================================================== PHASE 2

    /**
     * Phase 2 — atomic commit. Only call this when has_errors === false.
     *
     * Runs inside a SINGLE database transaction that is ROLLED BACK on any
     * exception. R2 keys uploaded during this call are also cleaned up on
     * rollback.
     *
     * Returns [
     *   'success'          => bool,
     *   'created_products' => int,
     *   'merged_products'  => int,
     *   'created_variants' => int,
     *   'updated_variants' => int,
     *   'created_factories'=> int,
     *   'errors'           => [],   // should be empty on success
     *   'image_stats'      => [...],
     * ]
     */
    public function commitImport(array $productsData, ?string $extractedZipDir = null): array
    {
        $createdProducts     = 0;
        $mergedProducts      = 0;
        $touchedProductIds   = [];
        $createdVariants     = 0;
        $updatedVariants     = 0;
        $createdFactories    = 0;
        $uploadedR2Keys      = []; // for rollback cleanup
        $imageMirrored       = 0;

        // ---- pre-load caches ----
        $factoryMapByCode = [];
        foreach ($this->factoryModel->all() as $f) {
            $factoryMapByCode[strtoupper($f['factory_code'])] = (int)$f['id'];
        }

        $vService = new \App\Services\VariationService();
        $fs       = new \App\Services\FilterAttributeService();

        // ---- acquire advisory lock to prevent concurrent imports colliding ----
        $lockAcquired = false;
        try {
            $lockRes = $this->db->query("SELECT GET_LOCK('importwala_bulk_import', 30)")->fetchColumn();
            $lockAcquired = ($lockRes == 1);
        } catch (\Throwable $e) {
            // non-fatal: continue without lock
        }

        try {
            // ---- ONE big transaction ----
            $this->db->beginTransaction();

            $existingImagesMap    = [];
            $existingColorsMap    = [];
            $existingColorSizesMap = [];

            foreach ($productsData as $prod) {
                if (($prod['status'] ?? '') === 'error') {
                    // should never happen since Phase 1 blocked commit when errors exist
                    continue;
                }

                // ---- factory ----
                $mfgIdCode = strtoupper(trim($prod['manufacturer_id_code'] ?? ''));
                $factoryId = null;
                if (!empty($mfgIdCode)) {
                    if (isset($factoryMapByCode[$mfgIdCode])) {
                        $factoryId = $factoryMapByCode[$mfgIdCode];
                    } else {
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
                        $factoryMapByCode[$code]      = $factoryId;
                        $factoryMapByCode[$mfgIdCode] = $factoryId;
                        $createdFactories++;
                    }
                } elseif (!empty($prod['factory_id'])) {
                    $factoryId = (int)$prod['factory_id'];
                }

                // ---- category / subcategory / brand ----
                $sku          = $prod['product_sku'];
                $categoryId   = $this->resolveOrCreateCategory($prod['category']);
                $subcategoryId = !empty($prod['subcategory']) ? $this->resolveOrCreateSubcategory($categoryId, $prod['subcategory']) : null;
                $brandId      = !empty($prod['brand']) ? $this->resolveOrCreateBrand($prod['brand']) : null;

                // ---- images ----
                $mainImagePath = $this->processImageSource($prod['main_image'], $extractedZipDir, $sku, 'main', $uploadedR2Keys);

                // ---- variation mode ----
                $varMode = 'none';
                if (!empty($prod['variants'])) {
                    $hasSizes = false;
                    foreach ($prod['variants'] as $v) {
                        if (!empty($v['size_label'])) { $hasSizes = true; break; }
                    }
                    $varMode = $hasSizes ? 'double' : 'single';
                }

                $slug = $this->slugify($prod['name']) . '-' . strtolower($sku);

                // ---- normalised title ----
                $normTitle = $this->normalizeTitle($prod['name']);

                // ---- upsert product ----
                $isExisting = !empty($prod['db_match_id']);
                $productId  = $isExisting ? (int)$prod['db_match_id'] : null;

                if ($isExisting) {
                    // MERGE: update only non-empty sheet values
                    $updateFields = [];
                    $updateParams = [':id' => $productId];

                    if (!empty($prod['name'])) { $updateFields[] = "name = :name"; $updateParams[':name'] = $prod['name']; }
                    if (!empty($slug)) { $updateFields[] = "slug = :slug"; $updateParams[':slug'] = $slug; }
                    if ($categoryId !== null) { $updateFields[] = "category_id = :category_id"; $updateParams[':category_id'] = $categoryId; }
                    if ($subcategoryId !== null) { $updateFields[] = "subcategory_id = :subcategory_id"; $updateParams[':subcategory_id'] = $subcategoryId; }
                    if ($brandId !== null) { $updateFields[] = "brand_id = :brand_id"; $updateParams[':brand_id'] = $brandId; }
                    if ($factoryId !== null) { $updateFields[] = "factory_id = :factory_id"; $updateParams[':factory_id'] = $factoryId; }
                    if (!empty($prod['weight'])) { $updateFields[] = "weight = :weight"; $updateParams[':weight'] = $prod['weight']; }
                    if (!empty($prod['variety'])) { $updateFields[] = "variety = :variety"; $updateParams[':variety'] = $prod['variety']; }
                    if (!empty($prod['description'])) { $updateFields[] = "description = :description"; $updateParams[':description'] = $prod['description']; }
                    $price = $prod['tier1_price'] > 0 ? $prod['tier1_price'] : $prod['one_piece_price'];
                    if ($price > 0) { $updateFields[] = "price = :price"; $updateParams[':price'] = $price; }
                    if ($prod['one_piece_price'] > 0) { $updateFields[] = "sale_price = :sale_price"; $updateParams[':sale_price'] = $prod['one_piece_price']; }
                    if (!empty($mainImagePath)) { $updateFields[] = "main_image = :main_image"; $updateParams[':main_image'] = $mainImagePath; }
                    if (!empty($prod['main_image'])) { $updateFields[] = "main_image_source_url = :main_image_source_url"; $updateParams[':main_image_source_url'] = $prod['main_image']; }
                    if (!empty($prod['manufacturer_id_code'])) { $updateFields[] = "manufacturer_id_code = :manufacturer_id_code"; $updateParams[':manufacturer_id_code'] = $prod['manufacturer_id_code']; }
                    if (!empty($prod['manufacturer_name'])) { $updateFields[] = "manufacturer_name = :manufacturer_name"; $updateParams[':manufacturer_name'] = $prod['manufacturer_name']; }
                    if (!empty($prod['source_platform'])) { $updateFields[] = "source_platform = :source_platform"; $updateParams[':source_platform'] = $prod['source_platform']; }
                    if (!empty($prod['source_product_id'])) { $updateFields[] = "source_product_id = :source_product_id"; $updateParams[':source_product_id'] = $prod['source_product_id']; }
                    if (!empty($prod['source_product_url'])) { $updateFields[] = "source_product_url = :source_product_url"; $updateParams[':source_product_url'] = $prod['source_product_url']; }
                    if (!empty($prod['import_date'])) { $updateFields[] = "import_date = :import_date"; $updateParams[':import_date'] = $prod['import_date']; }
                    if (!empty($prod['admin_status'])) { $updateFields[] = "admin_status = :admin_status"; $updateParams[':admin_status'] = $prod['admin_status']; }
                    $updateFields[] = "normalized_title = :normalized_title"; $updateParams[':normalized_title'] = $normTitle;
                    $updateFields[] = "variation_mode = :variation_mode"; $updateParams[':variation_mode'] = $varMode;
                    $updateFields[] = "updated_at = NOW()";

                    if (!empty($updateFields)) {
                        $sql = "UPDATE products SET " . implode(', ', $updateFields) . " WHERE id = :id";
                        $this->db->prepare($sql)->execute($updateParams);
                    }
                    $mergedProducts++;
                } else {
                    // INSERT new product (ensure slug is unique)
                    $baseSlug  = $slug;
                    $counter   = 1;
                    $slugStmt  = $this->db->prepare("SELECT id FROM products WHERE slug = ? LIMIT 1");
                    while (true) {
                        $slugStmt->execute([$slug]);
                        if ($slugStmt->fetchColumn()) {
                            $slug = $baseSlug . '-' . $counter++;
                        } else {
                            break;
                        }
                    }

                    $this->db->prepare("
                        INSERT INTO products (
                            name, slug, sku, category_id, subcategory_id, brand_id, factory_id,
                            weight, variety, description, price, sale_price, moq, stock,
                            main_image, main_image_source_url, video_url,
                            manufacturer_id_code, manufacturer_name, manufacturer_contact_person,
                            manufacturer_phone, manufacturer_whatsapp, manufacturer_email,
                            manufacturer_store_url, source_platform, source_product_id,
                            source_product_url, import_date, admin_status,
                            normalized_title, status, variation_mode,
                            created_at, updated_at
                        ) VALUES (
                            :name, :slug, :sku, :category_id, :subcategory_id, :brand_id, :factory_id,
                            :weight, :variety, :description, :price, :sale_price, :moq, :stock,
                            :main_image, :main_image_source_url, :video_url,
                            :manufacturer_id_code, :manufacturer_name, :manufacturer_contact_person,
                            :manufacturer_phone, :manufacturer_whatsapp, :manufacturer_email,
                            :manufacturer_store_url, :source_platform, :source_product_id,
                            :source_product_url, :import_date, :admin_status,
                            :normalized_title, 'active', :variation_mode,
                            NOW(), NOW()
                        )
                    ")->execute([
                        ':name'                        => $prod['name'],
                        ':slug'                        => $slug,
                        ':sku'                         => $sku,
                        ':category_id'                 => $categoryId,
                        ':subcategory_id'              => $subcategoryId,
                        ':brand_id'                    => $brandId,
                        ':factory_id'                  => $factoryId,
                        ':weight'                      => $prod['weight'] ?: null,
                        ':variety'                     => $prod['variety'] ?: null,
                        ':description'                 => $prod['description'] ?: null,
                        ':price'                       => $prod['tier1_price'] > 0 ? $prod['tier1_price'] : $prod['one_piece_price'],
                        ':sale_price'                  => $prod['one_piece_price'],
                        ':moq'                         => $prod['moq'],
                        ':stock'                       => $prod['available_qty'],
                        ':main_image'                  => !empty($mainImagePath) ? $mainImagePath : 'assets/images/placeholder.jpg',
                        ':main_image_source_url'       => $prod['main_image'] ?: null,
                        ':video_url'                   => $prod['video_url'] ?: null,
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
                        ':normalized_title'            => $normTitle,
                        ':variation_mode'              => $varMode,
                    ]);
                    $productId = (int)$this->db->lastInsertId();
                    $createdProducts++;
                }

                // ---- wholesale tiers ----
                $this->syncWholesaleTiers($productId, $prod);

                // ---- product specifications ----
                $this->syncProductSpecifications($productId, $prod);

                // ---- filter options ----
                $this->syncFilterOptions($productId, $prod, $fs);

                // ---- images ----
                if (!isset($existingImagesMap[$productId])) {
                    $existingImagesMap[$productId] = [];
                    $stmt = $this->db->prepare("SELECT image_path, image_url FROM product_images WHERE product_id = ?");
                    $stmt->execute([$productId]);
                    foreach ($stmt->fetchAll() as $imgRow) {
                        $existingImagesMap[$productId][] = $imgRow['image_path'];
                        $existingImagesMap[$productId][] = $imgRow['image_url'];
                    }
                }

                $imagesToInsert = [];
                $imgExists = fn($pid, $path) => in_array($path, $existingImagesMap[$pid] ?? []);

                if (!empty($mainImagePath) && !$imgExists($productId, $mainImagePath)) {
                    $imagesToInsert[] = [$productId, $mainImagePath, $mainImagePath, 0, 1];
                    $existingImagesMap[$productId][] = $mainImagePath;
                }
                if (!empty($prod['additional_images'])) {
                    foreach (array_map('trim', explode(',', $prod['additional_images'])) as $gIdx => $gImgName) {
                        if (empty($gImgName)) continue;
                        $gImgPath = $this->processImageSource($gImgName, $extractedZipDir, $sku, 'gallery-' . $gIdx, $uploadedR2Keys);
                        if ($gImgPath && $gImgPath !== $mainImagePath && !$imgExists($productId, $gImgPath)) {
                            $imagesToInsert[] = [$productId, $gImgPath, $gImgPath, $gIdx + 1, 0];
                            $existingImagesMap[$productId][] = $gImgPath;
                        }
                    }
                }
                if (!empty($imagesToInsert)) {
                    $qmarks  = implode(',', array_fill(0, count($imagesToInsert), '(?,?,?,?,?)'));
                    $flatArgs = [];
                    foreach ($imagesToInsert as $ia) { $flatArgs = array_merge($flatArgs, $ia); }
                    $this->db->prepare("INSERT INTO product_images (product_id, image_url, image_path, sort_order, is_primary) VALUES $qmarks")->execute($flatArgs);
                }

                // ---- variants ----
                if (!isset($existingColorsMap[$productId])) {
                    $existingColorsMap[$productId] = [];
                    $stmt = $this->db->prepare("SELECT id, LOWER(color_name) as cname FROM product_colors WHERE product_id = ?");
                    $stmt->execute([$productId]);
                    foreach ($stmt->fetchAll() as $cRow) {
                        $existingColorsMap[$productId][$cRow['cname']] = (int)$cRow['id'];
                    }
                }

                if ($varMode === 'single') {
                    $cStmtUpd = $this->db->prepare("UPDATE product_colors SET sku = :sku, price = :price, stock_qty = :stock, swatch_hex_or_image = COALESCE(:swatch, swatch_hex_or_image) WHERE id = :id");
                    $cStmtIns = $this->db->prepare("INSERT INTO product_colors (product_id, color_name, swatch_hex_or_image, sku, price, stock_qty) VALUES (:product_id, :color_name, :swatch, :sku, :price, :stock)");

                    foreach ($prod['variants'] as $vData) {
                        if (!empty($vData['_skip'])) continue;
                        $colorName = !empty($vData['color_name']) ? $vData['color_name'] : 'Default';
                        $swatchHex = !empty($vData['variant_image']) ? $this->processImageSource($vData['variant_image'], $extractedZipDir, $sku, 'variant-' . $vData['variant_sku'], $uploadedR2Keys) : null;
                        $cKey      = strtolower($colorName);

                        if (isset($existingColorsMap[$productId][$cKey])) {
                            $cStmtUpd->execute([
                                ':sku'    => $vData['variant_sku'],
                                ':price'  => $vData['variant_price'],
                                ':stock'  => $vData['variant_stock'],
                                ':swatch' => $swatchHex ?: null,
                                ':id'     => $existingColorsMap[$productId][$cKey],
                            ]);
                            $updatedVariants++;
                        } else {
                            $cStmtIns->execute([
                                ':product_id' => $productId,
                                ':color_name' => $colorName,
                                ':swatch'     => $swatchHex,
                                ':sku'        => $vData['variant_sku'],
                                ':price'      => $vData['variant_price'],
                                ':stock'      => $vData['variant_stock'],
                            ]);
                            $existingColorsMap[$productId][$cKey] = (int)$this->db->lastInsertId();
                            $createdVariants++;
                        }
                    }

                } elseif ($varMode === 'double') {
                    if (!isset($existingColorSizesMap[$productId])) {
                        $existingColorSizesMap[$productId] = [];
                        $stmt = $this->db->prepare("SELECT pcs.id, pcs.color_id, LOWER(pcs.size_label) as slabel FROM product_color_sizes pcs WHERE pcs.color_id IN (SELECT id FROM product_colors WHERE product_id = ?)");
                        $stmt->execute([$productId]);
                        foreach ($stmt->fetchAll() as $sRow) {
                            $existingColorSizesMap[$productId][$sRow['color_id'] . '_' . $sRow['slabel']] = (int)$sRow['id'];
                        }
                    }

                    $cStmtIns = $this->db->prepare("INSERT INTO product_colors (product_id, color_name, swatch_hex_or_image) VALUES (?, ?, ?)");
                    $sStmtUpd = $this->db->prepare("UPDATE product_color_sizes SET sku = :sku, price = :price, stock_qty = :stock WHERE id = :id");
                    $sStmtIns = $this->db->prepare("INSERT INTO product_color_sizes (color_id, size_label, sku, price, stock_qty) VALUES (:color_id, :size_label, :sku, :price, :stock)");

                    foreach ($prod['variants'] as $vData) {
                        if (!empty($vData['_skip'])) continue;
                        $colorName = !empty($vData['color_name']) ? $vData['color_name'] : 'Default';
                        $sizeLabel = !empty($vData['size_label']) ? $vData['size_label'] : 'Standard';
                        $swatchHex = !empty($vData['variant_image']) ? $this->processImageSource($vData['variant_image'], $extractedZipDir, $sku, 'variant-' . $vData['variant_sku'], $uploadedR2Keys) : null;
                        $cKey      = strtolower($colorName);

                        if (isset($existingColorsMap[$productId][$cKey])) {
                            $colorId = $existingColorsMap[$productId][$cKey];
                            if (!empty($swatchHex)) {
                                $this->db->prepare("UPDATE product_colors SET swatch_hex_or_image = ? WHERE id = ? AND (swatch_hex_or_image IS NULL OR swatch_hex_or_image = '')")
                                    ->execute([$swatchHex, $colorId]);
                            }
                        } else {
                            $cStmtIns->execute([$productId, $colorName, $swatchHex]);
                            $colorId = (int)$this->db->lastInsertId();
                            $existingColorsMap[$productId][$cKey] = $colorId;
                        }

                        $sKey = $colorId . '_' . strtolower($sizeLabel);
                        if (isset($existingColorSizesMap[$productId][$sKey])) {
                            $sStmtUpd->execute([
                                ':sku'   => $vData['variant_sku'],
                                ':price' => $vData['variant_price'],
                                ':stock' => $vData['variant_stock'],
                                ':id'    => $existingColorSizesMap[$productId][$sKey],
                            ]);
                            $existingColorSizesMap[$productId][$sKey . '_updated'] = true;
                            $updatedVariants++;
                        } else {
                            $sStmtIns->execute([
                                ':color_id'   => $colorId,
                                ':size_label' => $sizeLabel,
                                ':sku'        => $vData['variant_sku'],
                                ':price'      => $vData['variant_price'],
                                ':stock'      => $vData['variant_stock'],
                            ]);
                            $existingColorSizesMap[$productId][$sKey] = (int)$this->db->lastInsertId();
                            $createdVariants++;
                        }
                    }
                }

                $vService->syncToFlatVariants($productId, $varMode);

                if (!empty($productId)) {
                    $touchedProductIds[(int) $productId] = true;
                }
            }

            // ---- commit ----
            $this->db->commit();

            // flush cache
            try { \App\Infrastructure\Cache\CacheManager::getInstance()->flush(); } catch (\Throwable $e) {}

            // NOTE: visual-search feature indexing is intentionally NOT done here.
            // It downloads + hashes every image and would block the commit response
            // for minutes on large sheets. The controller runs it after the HTTP
            // response has been flushed (see BulkProductImportController::commit).

        } catch (\Throwable $e) {
            if ($this->db->inTransaction()) {
                $this->db->rollBack();
            }
            // clean up any R2 keys we uploaded before the exception
            $this->rollbackR2Uploads($uploadedR2Keys);

            return [
                'success' => false,
                'error'   => 'Transaction failed and was fully rolled back: ' . $e->getMessage(),
                'errors'  => [['reason' => $e->getMessage()]],
            ];
        } finally {
            if ($lockAcquired) {
                try { $this->db->query("SELECT RELEASE_LOCK('importwala_bulk_import')")->fetchColumn(); } catch (\Throwable $e) {}
            }
        }

        return [
            'success'           => true,
            'created_products'  => $createdProducts,
            'merged_products'   => $mergedProducts,
            'updated_products'  => $mergedProducts,
            'created_variants'  => $createdVariants,
            'updated_variants'  => $updatedVariants,
            'created_factories' => $createdFactories,
            'errors'            => [],
            'image_stats'       => ['mirrored' => $imageMirrored, 'failed' => 0],
            'visual_index_product_ids' => array_keys($touchedProductIds),
            'summary'           => [
                'created_products' => $createdProducts,
                'updated_products' => $mergedProducts,
                'created_variants' => $createdVariants,
                'updated_variants' => $updatedVariants,
                'created_factories'=> $createdFactories,
            ],
        ];
    }

    // ================================================================== HELPERS

    /**
     * Generate error XLSX report from validation errors.
     * Returns path to temp file.
     */
    public function generateErrorReport(array $products, array $globalErrors = []): string
    {
        $spreadsheet = new Spreadsheet();
        $sheet       = $spreadsheet->getActiveSheet();
        $sheet->setTitle('Import Errors');

        $headers = ['Sheet Row', 'Product SKU', 'Product Name', 'Column', 'Error / Warning', 'Severity'];
        foreach ($headers as $i => $h) {
            $col = \PhpOffice\PhpSpreadsheet\Cell\Coordinate::stringFromColumnIndex($i + 1);
            $sheet->setCellValue($col . '1', $h);
            $sheet->getStyle($col . '1')->getFont()->setBold(true);
            $sheet->getStyle($col . '1')->getFill()->setFillType(\PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID)
                ->getStartColor()->setARGB('FF334155');
            $sheet->getStyle($col . '1')->getFont()->getColor()->setARGB('FFFFFFFF');
        }

        $rowNum = 2;

        foreach ($globalErrors as $err) {
            $sheet->setCellValue('A' . $rowNum, '-');
            $sheet->setCellValue('B' . $rowNum, '-');
            $sheet->setCellValue('C' . $rowNum, 'GLOBAL');
            $sheet->setCellValue('D' . $rowNum, $err['column'] ?? '');
            $sheet->setCellValue('E' . $rowNum, $err['reason'] ?? (string)$err);
            $sheet->setCellValue('F' . $rowNum, 'ERROR');
            $sheet->getStyle('F' . $rowNum)->getFont()->setColor(new \PhpOffice\PhpSpreadsheet\Style\Color('FFCC0000'));
            $rowNum++;
        }

        foreach ($products as $prod) {
            foreach (($prod['errors'] ?? []) as $err) {
                $sheet->setCellValue('A' . $rowNum, is_array($err) ? ($err['row'] ?? '') : '');
                $sheet->setCellValue('B' . $rowNum, is_array($err) ? ($err['sku'] ?? $prod['product_sku'] ?? '') : $prod['product_sku'] ?? '');
                $sheet->setCellValue('C' . $rowNum, is_array($err) ? ($err['name'] ?? $prod['name'] ?? '') : $prod['name'] ?? '');
                $sheet->setCellValue('D' . $rowNum, is_array($err) ? ($err['column'] ?? '') : '');
                $sheet->setCellValue('E' . $rowNum, is_array($err) ? ($err['reason'] ?? '') : (string)$err);
                $sheet->setCellValue('F' . $rowNum, 'ERROR');
                $sheet->getStyle('F' . $rowNum)->getFont()->setColor(new \PhpOffice\PhpSpreadsheet\Style\Color('FFCC0000'));
                $rowNum++;
            }
            foreach (($prod['warnings'] ?? []) as $warn) {
                $sheet->setCellValue('A' . $rowNum, is_array($warn) ? ($warn['row'] ?? '') : '');
                $sheet->setCellValue('B' . $rowNum, is_array($warn) ? ($warn['sku'] ?? $prod['product_sku'] ?? '') : $prod['product_sku'] ?? '');
                $sheet->setCellValue('C' . $rowNum, is_array($warn) ? ($warn['name'] ?? $prod['name'] ?? '') : $prod['name'] ?? '');
                $sheet->setCellValue('D' . $rowNum, is_array($warn) ? ($warn['column'] ?? '') : '');
                $sheet->setCellValue('E' . $rowNum, is_array($warn) ? ($warn['reason'] ?? '') : (string)$warn);
                $sheet->setCellValue('F' . $rowNum, 'WARNING');
                $sheet->getStyle('F' . $rowNum)->getFont()->setColor(new \PhpOffice\PhpSpreadsheet\Style\Color('FFCC7700'));
                $rowNum++;
            }
        }

        foreach (range(1, 6) as $i) {
            $sheet->getColumnDimension(\PhpOffice\PhpSpreadsheet\Cell\Coordinate::stringFromColumnIndex($i))->setAutoSize(true);
        }

        $tmpPath = sys_get_temp_dir() . '/importwala_errors_' . time() . '.xlsx';
        (new Xlsx($spreadsheet))->save($tmpPath);
        return $tmpPath;
    }

    // ------------------------------------------------------------------ DB helpers

    /**
     * Load all products into a multi-index structure for duplicate detection.
     * Returns [
     *   'by_sku'         => [sku => row],
     *   'by_source'      => ['platform_productId' => row],
     *   'by_norm_title'  => ['catId_subcatId_normTitle' => row],
     * ]
     */
    private function loadAllProductsForDuplicateCheck(): array
    {
        $stmt = $this->db->query("
            SELECT id, name, sku, normalized_title, category_id, subcategory_id,
                   source_product_id, source_platform
            FROM products
            ORDER BY id
        ");
        $rows = $stmt->fetchAll(\PDO::FETCH_ASSOC);

        $result = ['by_sku' => [], 'by_source' => [], 'by_norm_title' => []];
        foreach ($rows as $row) {
            $result['by_sku'][$row['sku']] = $row;

            if (!empty($row['source_product_id']) && !empty($row['source_platform'])) {
                $sourceKey = strtolower($row['source_platform']) . '_' . $row['source_product_id'];
                $result['by_source'][$sourceKey] = $row;
            }

            $nt = $row['normalized_title'] ?? $this->normalizeTitle($row['name']);
            if ($nt !== '') {
                $titleKey = ($row['category_id'] ?? 0) . '_' . ($row['subcategory_id'] ?? 0) . '_' . $nt;
                if (!isset($result['by_norm_title'][$titleKey])) {
                    $result['by_norm_title'][$titleKey] = $row;
                }
            }
        }
        return $result;
    }

    /**
     * Load all variant SKUs from DB (product_colors + product_color_sizes + product_variants).
     * Returns [sku => product_id]
     */
    private function loadAllVariantSkusFromDb(): array
    {
        $skus = [];
        // product_colors (single-variant mode)
        foreach ($this->db->query("SELECT pc.sku, pc.product_id FROM product_colors pc WHERE pc.sku IS NOT NULL AND pc.sku <> ''")->fetchAll(\PDO::FETCH_ASSOC) as $r) {
            $skus[$r['sku']] = (int)$r['product_id'];
        }
        // product_color_sizes (double-variant mode)
        foreach ($this->db->query("SELECT pcs.sku, pc.product_id FROM product_color_sizes pcs JOIN product_colors pc ON pc.id = pcs.color_id WHERE pcs.sku IS NOT NULL AND pcs.sku <> ''")->fetchAll(\PDO::FETCH_ASSOC) as $r) {
            $skus[$r['sku']] = (int)$r['product_id'];
        }
        // product_variants (flat table)
        foreach ($this->db->query("SELECT sku, product_id FROM product_variants WHERE sku IS NOT NULL AND sku <> ''")->fetchAll(\PDO::FETCH_ASSOC) as $r) {
            if (!isset($skus[$r['sku']])) {
                $skus[$r['sku']] = (int)$r['product_id'];
            }
        }
        return $skus;
    }

    /**
     * Find a DB match for the given product using the three-key strategy.
     */
    private function findDbMatch(array $prod, array $dbProducts, string $normTitle): ?array
    {
        // Key 1: source_product_id + source_platform
        if (!empty($prod['source_product_id']) && !empty($prod['source_platform'])) {
            $sourceKey = strtolower($prod['source_platform']) . '_' . $prod['source_product_id'];
            if (isset($dbProducts['by_source'][$sourceKey])) {
                return $dbProducts['by_source'][$sourceKey];
            }
        }

        // Key 2: product SKU exists in DB for the same product (exact sku match)
        if (!empty($prod['product_sku']) && isset($dbProducts['by_sku'][$prod['product_sku']])) {
            return $dbProducts['by_sku'][$prod['product_sku']];
        }

        // Key 3: normalized title within same category + subcategory
        if ($normTitle !== '' && !empty($prod['_resolved_cat_id'])) {
            $catId    = $prod['_resolved_cat_id'];
            $subCatId = $prod['_resolved_subcat_id'] ?? 0;
            $titleKey = $catId . '_' . $subCatId . '_' . $normTitle;
            if (isset($dbProducts['by_norm_title'][$titleKey])) {
                return $dbProducts['by_norm_title'][$titleKey];
            }
        }

        return null;
    }

    // ------------------------------------------------------------------ image helpers

    /**
     * Enqueue image for R2 mirroring. Records the object key for rollback.
     */
    private function processImageSource(string $imageInput, ?string $extractedZipDir, string $sku = 'unknown', string $type = 'img', array &$uploadedR2Keys = []): string
    {
        $clean = trim($imageInput);
        if (empty($clean)) return '';

        if (preg_match('#^(www\.|[a-zA-Z0-9-]+\.[a-zA-Z]{2,}(?:/|$))#i', $clean) && !preg_match('#^https?://#i', $clean)) {
            $clean = 'https://' . $clean;
        }

        if (strpos($clean, 'r2.dev') !== false || strpos($clean, 'cloudflare') !== false) {
            return $clean;
        }

        if (filter_var($clean, FILTER_VALIDATE_URL)) {
            $enqueuedUrl = \App\Helpers\ImageMirror::enqueue($clean);
            // Track R2 key for potential rollback (best-effort)
            if (!empty($enqueuedUrl)) {
                $uploadedR2Keys[] = $enqueuedUrl;
            }
            return $enqueuedUrl;
        }

        if ($extractedZipDir) {
            $resolved = $this->resolveZipImage($clean, $extractedZipDir, $uploadedR2Keys);
            if ($resolved) return $resolved;
        }

        return '';
    }

    private function resolveZipImage(string $imageFilename, string $extractedZipDir, array &$uploadedR2Keys = []): ?string
    {
        $cleanName = basename($imageFilename);
        $paths     = [
            $extractedZipDir . '/' . $cleanName,
            $extractedZipDir . '/images/' . $cleanName,
            $extractedZipDir . '/img/' . $cleanName,
        ];

        foreach ($paths as $p) {
            if (!file_exists($p)) continue;

            $targetDir   = __DIR__ . '/../../public/uploads/products/';
            if (!is_dir($targetDir)) { mkdir($targetDir, 0755, true); }
            $ext         = pathinfo($cleanName, PATHINFO_EXTENSION) ?: 'jpg';
            $newFilename = 'bulk_' . time() . '_' . md5($cleanName) . '.' . $ext;
            $dest        = $targetDir . $newFilename;
            copy($p, $dest);

            try {
                if (class_exists('\\App\\Services\\CloudflareR2')) {
                    $r2       = new \App\Services\CloudflareR2();
                    $mimeType = function_exists('mime_content_type') ? mime_content_type($dest) : 'image/jpeg';
                    $r2Key    = 'products/' . $newFilename;
                    $r2->uploadFile($dest, $r2Key, $mimeType ?: 'image/jpeg');
                    $uploadedR2Keys[] = $r2Key;
                }
            } catch (\Throwable $e) {
                // log silently
            }

            return '/uploads/products/' . $newFilename;
        }
        return null;
    }

    /**
     * Best-effort cleanup of R2 objects uploaded before a transaction rollback.
     */
    private function rollbackR2Uploads(array $keys): void
    {
        if (empty($keys)) return;
        try {
            if (class_exists('\\App\\Services\\CloudflareR2')) {
                $r2 = new \App\Services\CloudflareR2();
                foreach ($keys as $key) {
                    try { $r2->delete($key); } catch (\Throwable $e) {}
                }
            }
        } catch (\Throwable $e) {}
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

    // ------------------------------------------------------------------ product helpers

    private function syncProductSpecifications(int $productId, array $prod): void
    {
        $specMap = [
            'Jewellery Type'                    => $prod['jewellery_type'] ?? '',
            'Gender'                            => $prod['gender'] ?? '',
            'Brand Name'                        => $prod['brand'] ?? '',
            'Material'                          => $prod['material'] ?? '',
            'Metal Type'                        => $prod['metal_type'] ?? '',
            'Metal Color'                       => $prod['metal_color'] ?? '',
            'Main Stone Type'                   => $prod['stone_type'] ?? '',
            'Size'                              => $prod['size'] ?? '',
            'Country of Origin'                 => $prod['country_of_origin'] ?? '',
            'Weight'                            => $prod['weight'] ?? '',
            'Variety'                           => $prod['variety'] ?? '',
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
                $specsToSave[] = ['key' => $k, 'value' => trim((string)$v)];
            }
        }
        $this->specModel->saveSpecifications($productId, $specsToSave);
    }

    private function syncFilterOptions(int $productId, array $prod, $fs = null): void
    {
        if (!$fs) { $fs = new \App\Services\FilterAttributeService(); }
        $fieldToSlug = [
            'category'              => 'category',
            'subcategory'           => 'subcategory',
            'jewellery_type'        => 'jewellery_type',
            'gender'                => 'gender',
            'material'              => 'material',
            'metal_type'            => 'metal_type',
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
            'manufacturer_name'     => 'manufacturer_name',
            'source_platform'       => 'source_platform',
            'admin_status'          => 'status',
        ];

        $attrData = [];
        foreach ($fieldToSlug as $field => $slug) {
            $rawValue = trim((string)($prod[$field] ?? ''));
            if ($rawValue === '') continue;
            $attr = $fs->getAttributeBySlug($slug);
            if (!$attr) continue;
            $attrId = (int)$attr['id'];
            foreach (preg_split('/[,;]+/', $rawValue) as $part) {
                $part = trim($part);
                $optId = $fs->getOrCreateOption($attrId, $part);
                if ($optId) { $attrData[$attrId][] = $optId; }
            }
        }
        if (!empty($attrData)) {
            $fs->saveProductAttributeValues($productId, $attrData);
        }
    }

    private function syncWholesaleTiers(int $productId, array $prod): void
    {
        $this->db->prepare("DELETE FROM tiered_prices WHERE product_id = ?")->execute([$productId]);

        $tiers = [];
        if (!empty($prod['tier1_qty']) && $prod['tier1_price'] > 0) {
            $r = $this->parseQtyRange($prod['tier1_qty'], 2, 35);
            $tiers[] = ['min_qty' => $r['min_qty'], 'max_qty' => $r['max_qty'], 'unit_price' => $prod['tier1_price']];
        }
        if (!empty($prod['tier2_qty']) && $prod['tier2_price'] > 0) {
            $r = $this->parseQtyRange($prod['tier2_qty'], 36, 149);
            $tiers[] = ['min_qty' => $r['min_qty'], 'max_qty' => $r['max_qty'], 'unit_price' => $prod['tier2_price']];
        }
        if (!empty($prod['tier3_qty']) && $prod['tier3_price'] > 0) {
            $r = $this->parseQtyRange($prod['tier3_qty'], 150, null);
            $tiers[] = ['min_qty' => $r['min_qty'], 'max_qty' => $r['max_qty'], 'unit_price' => $prod['tier3_price']];
        }
        if (!empty($tiers)) {
            $qmarks  = implode(',', array_fill(0, count($tiers), '(?,?,?,?)'));
            $flatArgs = [];
            foreach ($tiers as $t) { $flatArgs = array_merge($flatArgs, [$productId, $t['min_qty'], $t['max_qty'], $t['unit_price']]); }
            $this->db->prepare("INSERT INTO tiered_prices (product_id, min_qty, max_qty, unit_price) VALUES $qmarks")->execute($flatArgs);
        }
    }

    private function parseQtyRange(string $rangeStr, int $defaultMin, ?int $defaultMax): array
    {
        $clean = trim(preg_replace('/[^0-9\-><+]/', '', $rangeStr));
        if (str_contains($clean, '-')) {
            $parts = explode('-', $clean);
            return ['min_qty' => max(1, (int)($parts[0] ?? $defaultMin)), 'max_qty' => !empty($parts[1]) ? (int)$parts[1] : null];
        } elseif (str_contains($clean, '>') || str_contains($clean, '+')) {
            $minVal = (int)preg_replace('/[^0-9]/', '', $clean);
            return ['min_qty' => max(1, $minVal ?: $defaultMin), 'max_qty' => null];
        }
        return ['min_qty' => $defaultMin, 'max_qty' => $defaultMax];
    }

    // ------------------------------------------------------------------ category / brand / factory helpers

    private function getCategoryCache(): array
    {
        if ($this->categoryCache === null) {
            $this->categoryCache = [];
            foreach ((new Category())->all() as $c) {
                $this->categoryCache[strtolower($c['name'])] = (int)$c['id'];
            }
        }
        return $this->categoryCache;
    }

    private function normalizeSubcategoryKey(string $name): string
    {
        $clean = strtolower(trim($name));
        $clean = preg_replace('/[^a-z0-9]+/', '', $clean);
        if (str_ends_with($clean, 'ies') && strlen($clean) > 4) { $clean = substr($clean, 0, -3) . 'y'; }
        elseif (str_ends_with($clean, 'es') && strlen($clean) > 4) {
            $base = substr($clean, 0, -2);
            $clean = preg_match('/(ch|sh|x|z|s)$/', $base) ? $base : substr($clean, 0, -1);
        } elseif (str_ends_with($clean, 's') && !str_ends_with($clean, 'ss') && strlen($clean) > 3) {
            $clean = substr($clean, 0, -1);
        }
        return $clean;
    }

    private function getSubcategoryCache(): array
    {
        if ($this->subcategoryCache === null) {
            $this->subcategoryCache = [];
            foreach ((new Subcategory())->all() as $sc) {
                $catId    = $sc['category_id'] ?? 0;
                $exactKey = $catId . '_' . strtolower(trim($sc['name'] ?? ''));
                $normKey  = $catId . '_' . $this->normalizeSubcategoryKey($sc['name'] ?? '');
                $subId    = (int)($sc['id'] ?? 0);
                $this->subcategoryCache[$exactKey] = $subId;
                if (!isset($this->subcategoryCache[$normKey])) {
                    $this->subcategoryCache[$normKey] = $subId;
                }
            }
        }
        return $this->subcategoryCache;
    }

    private function getBrandCache(): array
    {
        if ($this->brandCache === null) {
            $this->brandCache = [];
            foreach ((new Brand())->all() as $b) {
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
        if (isset($cache[$lower])) return $cache[$lower];

        $catModel = new Category();
        $baseSlug = $this->slugify($clean);
        $slug     = $baseSlug;
        $counter  = 1;
        while ($catModel->slugExists($slug)) { $slug = $baseSlug . '-' . $counter++; }

        $catId = $catModel->insert(['name' => $clean, 'slug' => $slug, 'status' => 'active', 'created_at' => date('Y-m-d H:i:s'), 'updated_at' => date('Y-m-d H:i:s')]);
        $this->categoryCache[$lower] = $catId;
        return $catId;
    }

    private function resolveOrCreateSubcategory(int $categoryId, string $subcatName): int
    {
        $clean    = trim($subcatName);
        $exactKey = $categoryId . '_' . strtolower($clean);
        $normKey  = $categoryId . '_' . $this->normalizeSubcategoryKey($clean);
        $cache    = $this->getSubcategoryCache();

        if (isset($cache[$exactKey])) return $cache[$exactKey];
        if (isset($cache[$normKey]))  return $cache[$normKey];

        $subcatModel = new Subcategory();
        $baseSlug    = $this->slugify($clean);
        $slug        = $baseSlug;
        $counter     = 1;
        while ($subcatModel->slugExists($slug)) { $slug = $baseSlug . '-' . $counter++; }

        $subId = $subcatModel->insert(['category_id' => $categoryId, 'name' => $clean, 'slug' => $slug, 'status' => 'active', 'created_at' => date('Y-m-d H:i:s'), 'updated_at' => date('Y-m-d H:i:s')]);
        $this->subcategoryCache[$exactKey] = $subId;
        $this->subcategoryCache[$normKey]  = $subId;
        return $subId;
    }

    private function resolveOrCreateBrand(string $brandName): int
    {
        $clean = trim($brandName);
        $lower = strtolower($clean);
        $cache = $this->getBrandCache();
        if (isset($cache[$lower])) return $cache[$lower];

        $brandModel = new Brand();
        $brandId    = $brandModel->insert(['name' => $clean, 'slug' => $this->slugify($clean), 'status' => 'active', 'created_at' => date('Y-m-d H:i:s'), 'updated_at' => date('Y-m-d H:i:s')]);
        $this->brandCache[$lower] = $brandId;
        return $brandId;
    }

    // ------------------------------------------------------------------ string helpers

    /**
     * Normalise a product title for duplicate detection:
     * lowercase, trim, collapse multiple spaces, strip punctuation.
     */
    public function normalizeTitle(string $title): string
    {
        if (empty(trim($title))) return '';
        $t = mb_strtolower(trim($title));
        $t = preg_replace('/[^\p{L}\p{N}\s]/u', '', $t);   // strip punctuation
        $t = preg_replace('/\s{2,}/', ' ', $t);              // collapse spaces
        return trim($t);
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
