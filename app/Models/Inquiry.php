<?php

namespace App\Models;

use App\Core\Model;
use PDO;

class Inquiry extends Model
{
    protected string $table = 'inquiries';

    /**
     * Generate unique Inquiry Number: INQ-YYYYMMDD-XXXXX
     */
    public function generateInquiryNumber(): string
    {
        $datePrefix = date('Ymd');
        $randomSeq = sprintf('%05d', rand(1, 99999));
        $number = "INQ-{$datePrefix}-{$randomSeq}";

        // Ensure uniqueness
        $stmt = $this->db->prepare("SELECT id FROM inquiries WHERE inquiry_number = ? LIMIT 1");
        $stmt->execute([$number]);
        if ($stmt->fetch()) {
            return $this->generateInquiryNumber();
        }

        return $number;
    }

    /**
     * Create a new Inquiry with child Inquiry Items (Snapshots)
     */
    public function createInquiry(array $customerData, array $items): array
    {
        $inquiryNumber = $this->generateInquiryNumber();
        $totalProducts = count($items);
        $totalQuantity = 0;

        foreach ($items as $item) {
            $totalQuantity += (int)($item['quantity'] ?? 1);
        }

        $this->db->beginTransaction();

        try {
            $stmt = $this->db->prepare("
                INSERT INTO inquiries (
                    inquiry_number, customer_name, phone, email, company_name, 
                    city, state, gst_number, business_type, customer_message, 
                    delivery_timeline, total_products, total_quantity, status, created_at
                ) VALUES (
                    ?, ?, ?, ?, ?, 
                    ?, ?, ?, ?, ?, 
                    ?, ?, ?, 'New', NOW()
                )
            ");

            $stmt->execute([
                $inquiryNumber,
                trim($customerData['customer_name'] ?? ''),
                trim($customerData['phone'] ?? ''),
                trim($customerData['email'] ?? ''),
                trim($customerData['company_name'] ?? ''),
                trim($customerData['city'] ?? ''),
                trim($customerData['state'] ?? ''),
                trim($customerData['gst_number'] ?? ''),
                trim($customerData['business_type'] ?? ''),
                trim($customerData['customer_message'] ?? ''),
                trim($customerData['delivery_timeline'] ?? ''),
                $totalProducts,
                $totalQuantity,
            ]);

            $inquiryId = (int)$this->db->lastInsertId();

            $itemStmt = $this->db->prepare("
                INSERT INTO inquiry_items (
                    inquiry_id, product_id, product_name_snapshot, sku_snapshot,
                    product_image_snapshot, variation_id, variation_name,
                    quantity, price_snapshot, created_at
                ) VALUES (
                    ?, ?, ?, ?,
                    ?, ?, ?,
                    ?, ?, NOW()
                )
            ");

            foreach ($items as $item) {
                $itemStmt->execute([
                    $inquiryId,
                    (int)($item['product_id'] ?? 0),
                    trim($item['product_name_snapshot'] ?? $item['title'] ?? 'Product #' . ($item['product_id'] ?? 0)),
                    trim($item['sku_snapshot'] ?? $item['sku'] ?? ''),
                    trim($item['product_image_snapshot'] ?? $item['main_image'] ?? ''),
                    !empty($item['variation_id']) ? (int)$item['variation_id'] : null,
                    trim($item['variation_name'] ?? ''),
                    max(1, (int)($item['quantity'] ?? 1)),
                    isset($item['price_snapshot']) ? (float)$item['price_snapshot'] : (float)($item['base_price'] ?? 0),
                ]);
            }

            $this->db->commit();

            return [
                'success' => true,
                'inquiry_id' => $inquiryId,
                'inquiry_number' => $inquiryNumber,
                'total_products' => $totalProducts,
                'total_quantity' => $totalQuantity
            ];

        } catch (\Throwable $e) {
            $this->db->rollBack();
            throw $e;
        }
    }

    /**
     * Get single inquiry with all its child snapshot items
     */
    public function getInquiryWithItems(int $id): ?array
    {
        $stmt = $this->db->prepare("SELECT * FROM inquiries WHERE id = ? LIMIT 1");
        $stmt->execute([$id]);
        $inquiry = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$inquiry) {
            return null;
        }

        $itemsStmt = $this->db->prepare("
            SELECT ii.*, p.slug as current_product_slug 
            FROM inquiry_items ii 
            LEFT JOIN products p ON ii.product_id = p.id 
            WHERE ii.inquiry_id = ? 
            ORDER BY ii.id ASC
        ");
        $itemsStmt->execute([$id]);
        $inquiry['items'] = $itemsStmt->fetchAll(PDO::FETCH_ASSOC) ?: [];

        return $inquiry;
    }

    /**
     * Get paginated filtered inquiries list for Admin Panel
     */
    public function getAllFiltered(
        string $search = '',
        string $status = '',
        string $businessType = '',
        string $dateFrom = '',
        string $dateTo = '',
        int $limit = 20,
        int $offset = 0
    ): array {
        $where = ["1=1"];
        $params = [];

        if (!empty($search)) {
            $where[] = "(
                i.inquiry_number LIKE :s 
                OR i.customer_name LIKE :s 
                OR i.phone LIKE :s 
                OR i.email LIKE :s 
                OR i.company_name LIKE :s 
                OR i.id IN (SELECT inquiry_id FROM inquiry_items WHERE product_name_snapshot LIKE :s OR sku_snapshot LIKE :s)
            )";
            $params['s'] = '%' . trim($search) . '%';
        }

        if (!empty($status)) {
            $where[] = "i.status = :status";
            $params['status'] = $status;
        }

        if (!empty($businessType)) {
            $where[] = "i.business_type = :btype";
            $params['btype'] = $businessType;
        }

        if (!empty($dateFrom)) {
            $where[] = "DATE(i.created_at) >= :date_from";
            $params['date_from'] = $dateFrom;
        }

        if (!empty($dateTo)) {
            $where[] = "DATE(i.created_at) <= :date_to";
            $params['date_to'] = $dateTo;
        }

        $whereSql = implode(" AND ", $where);

        // Count total
        $countStmt = $this->db->prepare("SELECT COUNT(*) FROM inquiries i WHERE {$whereSql}");
        $countStmt->execute($params);
        $total = (int)$countStmt->fetchColumn();

        // Fetch paginated rows
        $sql = "SELECT i.* FROM inquiries i WHERE {$whereSql} ORDER BY i.id DESC LIMIT {$limit} OFFSET {$offset}";
        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);
        $items = $stmt->fetchAll(PDO::FETCH_ASSOC) ?: [];

        return [
            'total' => $total,
            'items' => $items,
            'limit' => $limit,
            'offset' => $offset,
        ];
    }

    /**
     * Update Inquiry Status
     */
    public function updateStatus(int $id, string $status): bool
    {
        $allowed = ['New', 'In Progress', 'Contacted', 'Quotation Sent', 'Converted', 'Closed', 'Rejected'];
        if (!in_array($status, $allowed)) {
            return false;
        }

        $stmt = $this->db->prepare("UPDATE inquiries SET status = ?, updated_at = NOW() WHERE id = ?");
        return $stmt->execute([$status, $id]);
    }

    /**
     * Update Admin Notes
     */
    public function updateNotes(int $id, string $notes): bool
    {
        $stmt = $this->db->prepare("UPDATE inquiries SET admin_notes = ?, updated_at = NOW() WHERE id = ?");
        return $stmt->execute([$notes, $id]);
    }

    /**
     * Delete Inquiry
     */
    public function deleteInquiry(int $id): bool
    {
        $stmt = $this->db->prepare("DELETE FROM inquiries WHERE id = ?");
        return $stmt->execute([$id]);
    }

    /**
     * Product-Level Inquiry Tracking Metrics
     */
    public function getProductInquiryStats(int $productId): array
    {
        $stmt = $this->db->prepare("
            SELECT 
                COUNT(DISTINCT inquiry_id) as total_inquiries,
                COALESCE(SUM(quantity), 0) as total_requested_units
            FROM inquiry_items 
            WHERE product_id = ?
        ");
        $stmt->execute([$productId]);
        $res = $stmt->fetch(PDO::FETCH_ASSOC);

        return [
            'total_inquiries' => (int)($res['total_inquiries'] ?? 0),
            'total_requested_units' => (int)($res['total_requested_units'] ?? 0),
        ];
    }

    /**
     * Count New Inquiries for Admin Sidebar Badge
     */
    public function getNewCount(): int
    {
        $stmt = $this->db->query("SELECT COUNT(*) FROM inquiries WHERE status = 'New'");
        return (int)$stmt->fetchColumn();
    }
}
