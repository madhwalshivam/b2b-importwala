<?php
namespace App\Core;

use PDO;

abstract class Model {
    protected PDO $db;
    protected string $table;
    protected string $primaryKey = 'id';

    public function __construct() {
        $this->db = Database::getInstance();
    }

    public function all(?string $orderBy = null): array {
        $order = $orderBy ?: "{$this->primaryKey} DESC";
        $stmt = $this->db->prepare("SELECT * FROM {$this->table} ORDER BY {$order}");
        $stmt->execute();
        return $stmt->fetchAll();
    }

    public function find(mixed $id): ?array {
        $stmt = $this->db->prepare("SELECT * FROM {$this->table} WHERE {$this->primaryKey} = ? LIMIT 1");
        $stmt->execute([$id]);
        $result = $stmt->fetch();
        return $result ?: null;
    }

    public function findBy(string $column, mixed $value): ?array {
        $stmt = $this->db->prepare("SELECT * FROM {$this->table} WHERE {$column} = ? LIMIT 1");
        $stmt->execute([$value]);
        $result = $stmt->fetch();
        return $result ?: null;
    }

    public function where(string $condition, array $params = [], ?string $orderBy = null): array {
        $order = $orderBy ?: "{$this->primaryKey} DESC";
        $stmt = $this->db->prepare("SELECT * FROM {$this->table} WHERE {$condition} ORDER BY {$order}");
        $stmt->execute($params);
        return $stmt->fetchAll();
    }

    public function insert(array $data): int {
        $columns = implode(', ', array_keys($data));
        $placeholders = implode(', ', array_fill(0, count($data), '?'));
        $stmt = $this->db->prepare("INSERT INTO {$this->table} ({$columns}) VALUES ({$placeholders})");
        $stmt->execute(array_values($data));
        return (int)$this->db->lastInsertId();
    }

    public function update(mixed $id, array $data): bool {
        $setClause = implode(' = ?, ', array_keys($data)) . ' = ?';
        $params = array_values($data);
        $params[] = $id;
        $stmt = $this->db->prepare("UPDATE {$this->table} SET {$setClause} WHERE {$this->primaryKey} = ?");
        return $stmt->execute($params);
    }

    public function delete(mixed $id): bool {
        $stmt = $this->db->prepare("DELETE FROM {$this->table} WHERE {$this->primaryKey} = ?");
        return $stmt->execute([$id]);
    }

    public function count(string $where = '1=1', array $params = []): int {
        $stmt = $this->db->prepare("SELECT COUNT(*) FROM {$this->table} WHERE {$where}");
        $stmt->execute($params);
        return (int)$stmt->fetchColumn();
    }

    public function paginate(int $page = 1, int $perPage = 12, string $where = '1=1', array $params = [], ?string $orderBy = null, string $columns = '*'): array {
        $total = $this->count($where, $params);
        $offset = ($page - 1) * $perPage;
        $order = $orderBy ?: "{$this->primaryKey} DESC";

        $sql = "SELECT {$columns} FROM {$this->table} WHERE {$where} ORDER BY {$order} LIMIT {$perPage} OFFSET {$offset}";
        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);
        $items = $stmt->fetchAll();

        return [
            'items' => $items,
            'total' => $total,
            'per_page' => $perPage,
            'current_page' => $page,
            'last_page' => (int)ceil($total / $perPage)
        ];
    }
}
