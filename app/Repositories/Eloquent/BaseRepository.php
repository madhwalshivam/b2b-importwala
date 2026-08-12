<?php

namespace App\Repositories\Eloquent;

use App\Core\Database;
use App\Repositories\Contracts\BaseRepositoryInterface;
use PDO;

abstract class BaseRepository implements BaseRepositoryInterface
{
    protected string $table;
    protected string $primaryKey = 'id';

    protected function getReadDb(): PDO
    {
        return Database::getReadConnection();
    }

    protected function getWriteDb(): PDO
    {
        return Database::getWriteConnection();
    }

    public function findById(int|string $id): ?array
    {
        $stmt = $this->getReadDb()->prepare("SELECT * FROM `{$this->table}` WHERE `{$this->primaryKey}` = :id LIMIT 1");
        $stmt->execute(['id' => $id]);
        $result = $stmt->fetch();
        return $result ?: null;
    }

    public function findAll(array $criteria = [], array $orderBy = [], int $limit = 50, int $offset = 0): array
    {
        $whereClauses = [];
        $bindings = [];

        foreach ($criteria as $column => $value) {
            $param = 'param_' . str_replace('.', '_', $column);
            $whereClauses[] = "`{$column}` = :{$param}";
            $bindings[$param] = $value;
        }

        $sql = "SELECT * FROM `{$this->table}`";
        if (!empty($whereClauses)) {
            $sql .= " WHERE " . implode(" AND ", $whereClauses);
        }

        if (!empty($orderBy)) {
            $orderParts = [];
            foreach ($orderBy as $col => $dir) {
                $direction = strtoupper($dir) === 'DESC' ? 'DESC' : 'ASC';
                $orderParts[] = "`{$col}` {$direction}";
            }
            $sql .= " ORDER BY " . implode(", ", $orderParts);
        }

        $sql .= " LIMIT {$limit} OFFSET {$offset}";

        $stmt = $this->getReadDb()->prepare($sql);
        $stmt->execute($bindings);
        return $stmt->fetchAll();
    }

    public function count(array $criteria = []): int
    {
        $whereClauses = [];
        $bindings = [];

        foreach ($criteria as $column => $value) {
            $param = 'param_' . str_replace('.', '_', $column);
            $whereClauses[] = "`{$column}` = :{$param}";
            $bindings[$param] = $value;
        }

        $sql = "SELECT COUNT(*) as total FROM `{$this->table}`";
        if (!empty($whereClauses)) {
            $sql .= " WHERE " . implode(" AND ", $whereClauses);
        }

        $stmt = $this->getReadDb()->prepare($sql);
        $stmt->execute($bindings);
        $row = $stmt->fetch();
        return (int)($row['total'] ?? 0);
    }

    public function create(array $data): int|string
    {
        $columns = array_keys($data);
        $fields = implode("`, `", $columns);
        $placeholders = ":" . implode(", :", $columns);

        $sql = "INSERT INTO `{$this->table}` (`{$fields}`) VALUES ({$placeholders})";
        $stmt = $this->getWriteDb()->prepare($sql);
        $stmt->execute($data);

        $lastId = $this->getWriteDb()->lastInsertId();
        return $lastId ?: ($data[$this->primaryKey] ?? 0);
    }

    public function update(int|string $id, array $data): bool
    {
        $assignments = [];
        $bindings = ['id' => $id];

        foreach ($data as $col => $val) {
            $assignments[] = "`{$col}` = :col_{$col}";
            $bindings["col_{$col}"] = $val;
        }

        $sql = "UPDATE `{$this->table}` SET " . implode(", ", $assignments) . " WHERE `{$this->primaryKey}` = :id";
        $stmt = $this->getWriteDb()->prepare($sql);
        return $stmt->execute($bindings);
    }

    public function delete(int|string $id): bool
    {
        $stmt = $this->getWriteDb()->prepare("DELETE FROM `{$this->table}` WHERE `{$this->primaryKey}` = :id");
        return $stmt->execute(['id' => $id]);
    }
}
