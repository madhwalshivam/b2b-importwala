<?php
namespace App\Models;

use App\Core\Model;

class Setting extends Model {
    protected string $table = 'settings';
    protected string $primaryKey = 'setting_key';

    public function getAllAsKeyValue(): array {
        $items = $this->all();
        $keyVal = [];
        foreach ($items as $item) {
            $keyVal[$item['setting_key']] = $item['setting_value'];
        }
        return $keyVal;
    }

    public static function set(string $key, mixed $value): void {
        try {
            $db = \App\Core\Database::getInstance();
            $stmt = $db->prepare("
                INSERT INTO settings (setting_key, setting_value) 
                VALUES (?, ?) 
                ON DUPLICATE KEY UPDATE setting_value = ?
            ");
            $stmt->execute([$key, (string)$value, (string)$value]);
        } catch (\Throwable $e) {
            error_log("Error saving setting {$key}: " . $e->getMessage());
        }
    }

    public static function get(string $key, mixed $default = null): mixed {
        try {
            $db = \App\Core\Database::getInstance();
            $stmt = $db->prepare("SELECT setting_value FROM settings WHERE setting_key = ? LIMIT 1");
            $stmt->execute([$key]);
            $val = $stmt->fetchColumn();
            return ($val !== false && $val !== null && $val !== '') ? $val : $default;
        } catch (\Throwable $e) {
            return $default;
        }
    }
}
