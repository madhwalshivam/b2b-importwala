<?php

namespace App\Models;

use App\Core\Model;

class Factory extends Model
{
    protected string $table = 'factories';

    /**
     * Generate next sequential Factory Code (e.g. FCT-001, FCT-002).
     * Guaranteed to never reuse a code even if a factory is deleted or archived.
     */
    public function generateNextCode(): string
    {
        $stmtF = $this->db->query("SELECT MAX(CAST(SUBSTRING(factory_code, 5) AS UNSIGNED)) FROM factories WHERE factory_code LIKE 'FCT-%'");
        $maxF = (int)$stmtF->fetchColumn();

        $stmtP = $this->db->query("SELECT MAX(CAST(SUBSTRING(manufacturer_id_code, 5) AS UNSIGNED)) FROM products WHERE manufacturer_id_code LIKE 'FCT-%'");
        $maxP = (int)$stmtP->fetchColumn();

        $nextNum = max($maxF, $maxP) + 1;
        if ($nextNum < 1) {
            $nextNum = 1;
        }

        // Loop safety check to ensure uniqueness
        do {
            $code = sprintf('FCT-%03d', $nextNum);
            $checkStmt = $this->db->prepare("SELECT COUNT(*) FROM factories WHERE factory_code = ?");
            $checkStmt->execute([$code]);
            $exists = (int)$checkStmt->fetchColumn() > 0;
            if ($exists) {
                $nextNum++;
            }
        } while ($exists);

        return $code;
    }

    /**
     * Get paginated factories with product counts.
     */
    public function getPaginatedFactories(string $search = '', string $status = '', int $page = 1, int $perPage = 20): array
    {
        $whereConditions = ["1=1"];
        $params = [];

        if (!empty($search)) {
            $whereConditions[] = "(f.factory_code LIKE ? OR f.name LIKE ? OR f.contact_person LIKE ? OR f.phone LIKE ? OR f.email LIKE ?)";
            $s = '%' . $search . '%';
            $params[] = $s;
            $params[] = $s;
            $params[] = $s;
            $params[] = $s;
            $params[] = $s;
        }

        if (!empty($status)) {
            $whereConditions[] = "f.status = ?";
            $params[] = $status;
        }

        $whereSql = implode(' AND ', $whereConditions);

        $countStmt = $this->db->prepare("SELECT COUNT(DISTINCT f.id) FROM factories f WHERE {$whereSql}");
        $countStmt->execute($params);
        $total = (int)$countStmt->fetchColumn();

        $offset = ($page - 1) * $perPage;
        $sql = "SELECT f.*, COUNT(p.id) as product_count
                FROM factories f
                LEFT JOIN products p ON p.factory_id = f.id
                WHERE {$whereSql}
                GROUP BY f.id
                ORDER BY f.id DESC
                LIMIT {$perPage} OFFSET {$offset}";

        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);
        $items = $stmt->fetchAll();

        return [
            'items'        => $items,
            'total'        => $total,
            'per_page'     => $perPage,
            'current_page' => $page,
            'last_page'    => (int)ceil($total / max(1, $perPage))
        ];
    }

    /**
     * Get isolated products for a specific factory.
     */
    public function getFactoryProducts(int $factoryId, string $search = '', string $status = '', int $page = 1, int $perPage = 20): array
    {
        $whereConditions = ["p.factory_id = ?"];
        $params = [$factoryId];

        if (!empty($search)) {
            $whereConditions[] = "(p.name LIKE ? OR p.sku LIKE ?)";
            $s = '%' . $search . '%';
            $params[] = $s;
            $params[] = $s;
        }

        if (!empty($status)) {
            $whereConditions[] = "p.status = ?";
            $params[] = $status;
        }

        $whereSql = implode(' AND ', $whereConditions);

        $countStmt = $this->db->prepare("SELECT COUNT(*) FROM products p WHERE {$whereSql}");
        $countStmt->execute($params);
        $total = (int)$countStmt->fetchColumn();

        $offset = ($page - 1) * $perPage;
        $sql = "SELECT p.*, c.name as category_name, b.name as brand_name
                FROM products p
                LEFT JOIN categories c ON p.category_id = c.id
                LEFT JOIN brands b ON p.brand_id = b.id
                WHERE {$whereSql}
                ORDER BY p.id DESC
                LIMIT {$perPage} OFFSET {$offset}";

        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);
        $items = $stmt->fetchAll();

        return [
            'items'        => $items,
            'total'        => $total,
            'per_page'     => $perPage,
            'current_page' => $page,
            'last_page'    => (int)ceil($total / max(1, $perPage))
        ];
    }

    /**
     * Find existing factory by code or create a new factory for bulk sheet import.
     */
    public function findOrCreateByManufacturerCode(string $mfgIdCode, array $details): array
    {
        $cleanCode = strtoupper(trim($mfgIdCode));
        
        // 1. Search by exact factory_code in DB
        if (!empty($cleanCode)) {
            $existing = $this->findBy('factory_code', $cleanCode);
            if ($existing) {
                return [
                    'factory' => $existing,
                    'is_new'  => false
                ];
            }
        }

        // 2. Auto-create new factory
        $newCode = !empty($cleanCode) && str_starts_with($cleanCode, 'FCT-') ? $cleanCode : $this->generateNextCode();
        
        $name = !empty($details['name']) ? trim($details['name']) : ('Factory ' . $newCode);
        
        $factoryData = [
            'factory_code'    => $newCode,
            'name'            => $name,
            'contact_person'  => $details['contact_person'] ?? null,
            'phone'           => $details['phone'] ?? null,
            'whatsapp'        => $details['whatsapp'] ?? null,
            'email'           => $details['email'] ?? null,
            'store_url'       => $details['store_url'] ?? null,
            'source_platform' => $details['source_platform'] ?? null,
            'status'          => 'active',
            'notes'           => 'Auto-created via Bulk Product Sheet Importer.',
            'created_at'      => date('Y-m-d H:i:s'),
            'updated_at'      => date('Y-m-d H:i:s'),
        ];

        $factoryId = $this->insert($factoryData);
        $factoryData['id'] = $factoryId;

        return [
            'factory' => $factoryData,
            'is_new'  => true
        ];
    }

    /**
     * Get all active factories ordered by name.
     */
    public function getActiveFactories(): array
    {
        return $this->where("status = 'active'", [], "name ASC");
    }
}
