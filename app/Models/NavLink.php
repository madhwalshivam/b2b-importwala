<?php
namespace App\Models;

use App\Core\Model;

class NavLink extends Model {
    protected string $table = 'nav_links';

    /**
     * Get menu tree (parent links with nested 'children' array).
     */
    public function getTree(bool $activeOnly = false): array {
        $where = $activeOnly ? "WHERE parent_id IS NULL AND is_active = 1" : "WHERE parent_id IS NULL";
        $stmt = $this->db->query("SELECT * FROM nav_links {$where} ORDER BY sort_order ASC, id ASC");
        $parents = $stmt->fetchAll() ?: [];

        foreach ($parents as &$parent) {
            $childWhere = $activeOnly ? "WHERE parent_id = ? AND is_active = 1" : "WHERE parent_id = ?";
            $childStmt = $this->db->prepare("SELECT * FROM nav_links {$childWhere} ORDER BY sort_order ASC, id ASC");
            $childStmt->execute([$parent['id']]);
            $parent['children'] = $childStmt->fetchAll() ?: [];
        }
        unset($parent);

        return $parents;
    }

    /**
     * Get all flat links sorted by sort_order.
     */
    public function getAllFlat(): array {
        $stmt = $this->db->query("
            SELECT n.*, p.label AS parent_label 
            FROM nav_links n 
            LEFT JOIN nav_links p ON n.parent_id = p.id 
            ORDER BY COALESCE(n.parent_id, n.id) ASC, n.parent_id IS NOT NULL ASC, n.sort_order ASC, n.id ASC
        ");
        return $stmt->fetchAll() ?: [];
    }

    /**
     * Find link by ID.
     */
    public function findById(int $id): ?array {
        $stmt = $this->db->prepare("SELECT * FROM nav_links WHERE id = ?");
        $stmt->execute([$id]);
        return $stmt->fetch() ?: null;
    }

    /**
     * Create a new nav link.
     */
    public function createLink(array $data): int {
        $parentId = !empty($data['parent_id']) ? (int)$data['parent_id'] : null;
        $sortOrder = isset($data['sort_order']) && $data['sort_order'] !== '' ? (int)$data['sort_order'] : $this->getNextSortOrder($parentId);
        $isActive = isset($data['is_active']) ? (int)$data['is_active'] : 1;
        $openInNewTab = isset($data['open_in_new_tab']) ? (int)$data['open_in_new_tab'] : 0;
        $type = !empty($data['type']) ? trim($data['type']) : 'internal';

        // Auto shift duplicate sort orders
        $this->normalizeSortOrdersOnInsert($parentId, $sortOrder);

        $stmt = $this->db->prepare("
            INSERT INTO nav_links (label, url, type, parent_id, sort_order, is_active, open_in_new_tab)
            VALUES (?, ?, ?, ?, ?, ?, ?)
        ");
        $stmt->execute([
            trim($data['label']),
            trim($data['url']),
            $type,
            $parentId,
            $sortOrder,
            $isActive,
            $openInNewTab
        ]);

        return (int)$this->db->lastInsertId();
    }

    /**
     * Update an existing nav link.
     */
    public function updateLink(int $id, array $data): bool {
        $existing = $this->findById($id);
        if (!$existing) return false;

        $parentId = array_key_exists('parent_id', $data) ? (!empty($data['parent_id']) ? (int)$data['parent_id'] : null) : $existing['parent_id'];
        $sortOrder = isset($data['sort_order']) && $data['sort_order'] !== '' ? (int)$data['sort_order'] : $existing['sort_order'];
        $isActive = isset($data['is_active']) ? (int)$data['is_active'] : $existing['is_active'];
        $openInNewTab = isset($data['open_in_new_tab']) ? (int)$data['open_in_new_tab'] : $existing['open_in_new_tab'];
        $label = !empty($data['label']) ? trim($data['label']) : $existing['label'];
        $url = isset($data['url']) ? trim($data['url']) : $existing['url'];
        $type = !empty($data['type']) ? trim($data['type']) : $existing['type'];

        // Prevent setting parent_id to itself or its child
        if ($parentId === $id) {
            $parentId = null;
        }

        $stmt = $this->db->prepare("
            UPDATE nav_links 
            SET label = ?, url = ?, type = ?, parent_id = ?, sort_order = ?, is_active = ?, open_in_new_tab = ?
            WHERE id = ?
        ");
        return $stmt->execute([
            $label,
            $url,
            $type,
            $parentId,
            $sortOrder,
            $isActive,
            $openInNewTab,
            $id
        ]);
    }

    /**
     * Delete a link.
     */
    public function deleteLink(int $id): bool {
        $stmt = $this->db->prepare("DELETE FROM nav_links WHERE id = ?");
        return $stmt->execute([$id]);
    }

    /**
     * Toggle active/inactive status.
     */
    public function toggleStatus(int $id): bool {
        $stmt = $this->db->prepare("UPDATE nav_links SET is_active = IF(is_active = 1, 0, 1) WHERE id = ?");
        return $stmt->execute([$id]);
    }

    /**
     * Reorder list of link IDs.
     */
    public function reorder(array $orderedIds): bool {
        $stmt = $this->db->prepare("UPDATE nav_links SET sort_order = ? WHERE id = ?");
        foreach ($orderedIds as $index => $id) {
            $stmt->execute([$index + 1, (int)$id]);
        }
        return true;
    }

    /**
     * Move link up/down relative to sibling links.
     */
    public function movePosition(int $id, string $direction): bool {
        $link = $this->findById($id);
        if (!$link) return false;

        $parentId = $link['parent_id'];
        $currentSort = (int)$link['sort_order'];

        if ($direction === 'up') {
            $stmt = $this->db->prepare($parentId === null
                ? "SELECT * FROM nav_links WHERE parent_id IS NULL AND sort_order < ? ORDER BY sort_order DESC LIMIT 1"
                : "SELECT * FROM nav_links WHERE parent_id = ? AND sort_order < ? ORDER BY sort_order DESC LIMIT 1"
            );
            $parentId === null ? $stmt->execute([$currentSort]) : $stmt->execute([$parentId, $currentSort]);
        } else {
            $stmt = $this->db->prepare($parentId === null
                ? "SELECT * FROM nav_links WHERE parent_id IS NULL AND sort_order > ? ORDER BY sort_order ASC LIMIT 1"
                : "SELECT * FROM nav_links WHERE parent_id = ? AND sort_order > ? ORDER BY sort_order ASC LIMIT 1"
            );
            $parentId === null ? $stmt->execute([$currentSort]) : $stmt->execute([$parentId, $currentSort]);
        }

        $swapTarget = $stmt->fetch();
        if (!$swapTarget) return false;

        // Swap sort_order
        $this->db->prepare("UPDATE nav_links SET sort_order = ? WHERE id = ?")->execute([(int)$swapTarget['sort_order'], $id]);
        $this->db->prepare("UPDATE nav_links SET sort_order = ? WHERE id = ?")->execute([$currentSort, $swapTarget['id']]);

        return true;
    }

    /**
     * Get next sort order for given parent.
     */
    public function getNextSortOrder(?int $parentId = null): int {
        if ($parentId === null) {
            $stmt = $this->db->query("SELECT MAX(sort_order) FROM nav_links WHERE parent_id IS NULL");
        } else {
            $stmt = $this->db->prepare("SELECT MAX(sort_order) FROM nav_links WHERE parent_id = ?");
            $stmt->execute([$parentId]);
        }
        $max = $stmt->fetchColumn();
        return ($max !== false && $max !== null) ? ((int)$max + 1) : 1;
    }

    /**
     * Shift sort order if duplicate position is given.
     */
    private function normalizeSortOrdersOnInsert(?int $parentId, int $targetSortOrder): void {
        if ($parentId === null) {
            $stmt = $this->db->prepare("UPDATE nav_links SET sort_order = sort_order + 1 WHERE parent_id IS NULL AND sort_order >= ?");
            $stmt->execute([$targetSortOrder]);
        } else {
            $stmt = $this->db->prepare("UPDATE nav_links SET sort_order = sort_order + 1 WHERE parent_id = ? AND sort_order >= ?");
            $stmt->execute([$parentId, $targetSortOrder]);
        }
    }
}
