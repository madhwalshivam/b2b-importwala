<?php
namespace App\Models;

use App\Core\Model;
use PDO;

class CollectionCard extends Model {
    protected string $table = 'collection_cards';

    /**
     * Sab collection cards (admin ke liye)
     */
    public function getAll(): array {
        $stmt = $this->db->query("SELECT * FROM collection_cards ORDER BY sort_order ASC, id ASC");
        return $stmt->fetchAll(PDO::FETCH_ASSOC) ?: [];
    }

    /**
     * Sirf active cards
     */
    public function getActive(): array {
        $stmt = $this->db->query("SELECT * FROM collection_cards WHERE is_active = 1 ORDER BY sort_order ASC, id ASC");
        return $stmt->fetchAll(PDO::FETCH_ASSOC) ?: [];
    }

    /**
     * Active cards with their products (frontend ke liye)
     */
    public function getActiveWithProducts(?int $limit = null): array {
        $cards = $this->getActive();
        if (empty($cards)) return [];

        $result = [];
        foreach ($cards as $card) {
            $products = $this->getCardProducts((int)$card['id'], $limit);
            $card['products'] = $products;
            $result[] = $card;
        }
        return $result;
    }

    /**
     * Ek card ke assigned products fetch karo
     */
    public function getCardProducts(int $cardId, ?int $limit = null): array {
        $limitClause = ($limit !== null && $limit > 0) ? "LIMIT {$limit}" : "";
        $sql = "
            SELECT p.id, p.name, p.slug, p.price, p.sale_price, p.main_image, p.sku,
                   c.name AS category_name,
                   ccp.display_order
            FROM collection_card_products ccp
            JOIN products p ON ccp.product_id = p.id
            LEFT JOIN categories c ON p.category_id = c.id
            WHERE ccp.collection_card_id = ? AND p.status = 'active'
            ORDER BY ccp.display_order ASC, ccp.id ASC
            {$limitClause}
        ";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([$cardId]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC) ?: [];
    }

    /**
     * ID se card fetch karo
     */
    public function findById(int $id): ?array {
        $stmt = $this->db->prepare("SELECT * FROM collection_cards WHERE id = ?");
        $stmt->execute([$id]);
        return $stmt->fetch(PDO::FETCH_ASSOC) ?: null;
    }

    /**
     * Naya card create karo
     */
    public function createCard(array $data): int {
        $stmt = $this->db->prepare("
            INSERT INTO collection_cards (title, subtitle, image, link_url, badge_text, sort_order, is_active)
            VALUES (?, ?, ?, ?, ?, ?, ?)
        ");
        $stmt->execute([
            $data['title'],
            $data['subtitle'] ?? null,
            $data['image'] ?? null,
            $data['link_url'] ?? '/catalog',
            $data['badge_text'] ?? null,
            (int)($data['sort_order'] ?? 0),
            isset($data['is_active']) ? (int)$data['is_active'] : 1,
        ]);
        return (int)$this->db->lastInsertId();
    }

    /**
     * Card update karo
     */
    public function updateCard(int $id, array $data): bool {
        $stmt = $this->db->prepare("
            UPDATE collection_cards
            SET title = ?, subtitle = ?, image = ?, link_url = ?, badge_text = ?, sort_order = ?, is_active = ?
            WHERE id = ?
        ");
        return $stmt->execute([
            $data['title'],
            $data['subtitle'] ?? null,
            $data['image'] ?? null,
            $data['link_url'] ?? '/catalog',
            $data['badge_text'] ?? null,
            (int)($data['sort_order'] ?? 0),
            isset($data['is_active']) ? (int)$data['is_active'] : 1,
            $id,
        ]);
    }

    /**
     * Card delete karo (products bhi cascade mein delete honge)
     */
    public function deleteCard(int $id): bool {
        $stmt = $this->db->prepare("DELETE FROM collection_cards WHERE id = ?");
        return $stmt->execute([$id]);
    }

    /**
     * Card ke products save karo (replace mode)
     */
    public function saveCardProducts(int $cardId, array $productIds): bool {
        $this->db->beginTransaction();
        try {
            // Pehle sab hata do
            $del = $this->db->prepare("DELETE FROM collection_card_products WHERE collection_card_id = ?");
            $del->execute([$cardId]);

            // Phir naye insert karo
            if (!empty($productIds)) {
                $ins = $this->db->prepare(
                    "INSERT INTO collection_card_products (collection_card_id, product_id, display_order) VALUES (?, ?, ?)"
                );
                $order = 1;
                foreach ($productIds as $pid) {
                    $pid = (int)$pid;
                    if ($pid > 0) {
                        $ins->execute([$cardId, $pid, $order++]);
                    }
                }
            }

            $this->db->commit();
            return true;
        } catch (\Throwable $e) {
            $this->db->rollBack();
            return false;
        }
    }

    /**
     * Sort order update karo (drag & drop reorder ke liye)
     */
    public function updateSortOrder(array $orderMap): void {
        $stmt = $this->db->prepare("UPDATE collection_cards SET sort_order = ? WHERE id = ?");
        foreach ($orderMap as $id => $order) {
            $stmt->execute([(int)$order, (int)$id]);
        }
    }
}
