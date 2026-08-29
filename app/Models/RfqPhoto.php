<?php

namespace App\Models;

use App\Core\Model;

class RfqPhoto extends Model
{
    protected string $table = 'rfq_reference_photos';

    /**
     * Save an uploaded photo record linked to an RFQ.
     */
    public function addPhoto(int $rfqId, string $filePath, string $originalName, int $fileSize = 0): int
    {
        $stmt = $this->db->prepare("
            INSERT INTO rfq_reference_photos (rfq_id, file_path, original_name, file_size, created_at)
            VALUES (?, ?, ?, ?, NOW())
        ");
        $stmt->execute([$rfqId, $filePath, $originalName, $fileSize]);
        return (int)$this->db->lastInsertId();
    }

    /**
     * Get all photos for an RFQ.
     */
    public function getByRfq(int $rfqId): array
    {
        $stmt = $this->db->prepare(
            "SELECT * FROM rfq_reference_photos WHERE rfq_id = ? ORDER BY id ASC"
        );
        $stmt->execute([$rfqId]);
        return $stmt->fetchAll(\PDO::FETCH_ASSOC) ?: [];
    }

    /**
     * Delete all photos for an RFQ and return file paths for filesystem cleanup.
     */
    public function deleteByRfq(int $rfqId): array
    {
        $photos = $this->getByRfq($rfqId);
        $paths  = array_column($photos, 'file_path');

        $stmt = $this->db->prepare("DELETE FROM rfq_reference_photos WHERE rfq_id = ?");
        $stmt->execute([$rfqId]);

        return $paths;
    }
}
