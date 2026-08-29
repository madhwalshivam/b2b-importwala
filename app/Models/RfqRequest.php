<?php

namespace App\Models;

use App\Core\Model;
use PDO;

class RfqRequest extends Model
{
    protected string $table = 'rfq_requests';

    /**
     * Create a new RFQ record and return its ID.
     */
    public function createRfq(array $data): int
    {
        $stmt = $this->db->prepare("
            INSERT INTO rfq_requests (
                product_name, product_reference_link, quantity, unit,
                target_price, overall_budget, sourcing_purpose, specifications,
                full_name, phone, email, pincode,
                business_type, has_gst, additional_comments,
                status, created_at
            ) VALUES (
                :product_name, :product_reference_link, :quantity, :unit,
                :target_price, :overall_budget, :sourcing_purpose, :specifications,
                :full_name, :phone, :email, :pincode,
                :business_type, :has_gst, :additional_comments,
                'New', NOW()
            )
        ");

        $stmt->execute([
            'product_name'           => trim($data['product_name'] ?? ''),
            'product_reference_link' => trim($data['product_reference_link'] ?? '') ?: null,
            'quantity'               => (int)($data['quantity'] ?? 0),
            'unit'                   => trim($data['unit'] ?? ''),
            'target_price'           => (float)($data['target_price'] ?? 0),
            'overall_budget'         => trim($data['overall_budget'] ?? ''),
            'sourcing_purpose'       => trim($data['sourcing_purpose'] ?? ''),
            'specifications'         => trim($data['specifications'] ?? '') ?: null,
            'full_name'              => trim($data['full_name'] ?? ''),
            'phone'                  => trim($data['phone'] ?? ''),
            'email'                  => trim($data['email'] ?? ''),
            'pincode'                => trim($data['pincode'] ?? ''),
            'business_type'          => trim($data['business_type'] ?? ''),
            'has_gst'                => !empty($data['has_gst']) && $data['has_gst'] === 'yes' ? 1 : 0,
            'additional_comments'    => trim($data['additional_comments'] ?? '') ?: null,
        ]);

        return (int)$this->db->lastInsertId();
    }

    /**
     * Get an RFQ with its reference photos.
     */
    public function getWithPhotos(int $id): ?array
    {
        $stmt = $this->db->prepare("SELECT * FROM rfq_requests WHERE id = ? LIMIT 1");
        $stmt->execute([$id]);
        $rfq = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$rfq) {
            return null;
        }

        $photoStmt = $this->db->prepare(
            "SELECT * FROM rfq_reference_photos WHERE rfq_id = ? ORDER BY id ASC"
        );
        $photoStmt->execute([$id]);
        $rfq['photos'] = $photoStmt->fetchAll(PDO::FETCH_ASSOC) ?: [];

        return $rfq;
    }

    /**
     * Get paginated, filtered list of RFQs for admin.
     */
    public function getAllFiltered(
        string $search = '',
        string $status = '',
        string $dateFrom = '',
        string $dateTo = '',
        int $limit = 20,
        int $offset = 0
    ): array {
        $where  = ['1=1'];
        $params = [];

        if (!empty($search)) {
            $where[]       = "(full_name LIKE :s OR phone LIKE :s OR email LIKE :s OR product_name LIKE :s)";
            $params['s']   = '%' . $search . '%';
        }

        if (!empty($status)) {
            $where[]          = "status = :status";
            $params['status'] = $status;
        }

        if (!empty($dateFrom)) {
            $where[]             = "DATE(created_at) >= :date_from";
            $params['date_from'] = $dateFrom;
        }

        if (!empty($dateTo)) {
            $where[]           = "DATE(created_at) <= :date_to";
            $params['date_to'] = $dateTo;
        }

        $whereSql = implode(' AND ', $where);

        $countStmt = $this->db->prepare("SELECT COUNT(*) FROM rfq_requests WHERE {$whereSql}");
        $countStmt->execute($params);
        $total = (int)$countStmt->fetchColumn();

        $stmt = $this->db->prepare(
            "SELECT * FROM rfq_requests WHERE {$whereSql} ORDER BY id DESC LIMIT {$limit} OFFSET {$offset}"
        );
        $stmt->execute($params);
        $items = $stmt->fetchAll(PDO::FETCH_ASSOC) ?: [];

        return ['total' => $total, 'items' => $items];
    }

    /**
     * Update the status of an RFQ.
     */
    public function updateStatus(int $id, string $status): bool
    {
        $allowed = ['New', 'Contacted', 'Quoted', 'Closed'];
        if (!in_array($status, $allowed, true)) {
            return false;
        }

        $stmt = $this->db->prepare(
            "UPDATE rfq_requests SET status = ?, updated_at = NOW() WHERE id = ?"
        );
        return $stmt->execute([$status, $id]);
    }

    /**
     * Update admin notes.
     */
    public function updateNotes(int $id, string $notes): bool
    {
        $stmt = $this->db->prepare(
            "UPDATE rfq_requests SET admin_notes = ?, updated_at = NOW() WHERE id = ?"
        );
        return $stmt->execute([$notes, $id]);
    }

    /**
     * Delete an RFQ (photos cascade via FK).
     */
    public function deleteRfq(int $id): bool
    {
        $stmt = $this->db->prepare("DELETE FROM rfq_requests WHERE id = ?");
        return $stmt->execute([$id]);
    }

    /**
     * Count RFQs with status = 'New' for admin badge.
     */
    public function getNewCount(): int
    {
        $stmt = $this->db->query("SELECT COUNT(*) FROM rfq_requests WHERE status = 'New'");
        return (int)$stmt->fetchColumn();
    }

    /**
     * Get all rows for CSV export (respects filters).
     */
    public function getForExport(
        string $search = '',
        string $status = '',
        string $dateFrom = '',
        string $dateTo = ''
    ): array {
        return $this->getAllFiltered($search, $status, $dateFrom, $dateTo, 10000, 0)['items'];
    }
}
