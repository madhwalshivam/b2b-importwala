<?php
namespace App\Models;

use App\Core\Model;

class UserAddress extends Model {
    protected string $table = 'user_addresses';

    public function getDefaultAddress(int $userId): ?array {
        $stmt = $this->db->prepare("
            SELECT * FROM user_addresses 
            WHERE user_id = ? 
            ORDER BY is_default DESC, updated_at DESC 
            LIMIT 1
        ");
        $stmt->execute([$userId]);
        $res = $stmt->fetch(\PDO::FETCH_ASSOC);
        return $res ?: null;
    }

    public function getUserAddresses(int $userId): array {
        $stmt = $this->db->prepare("
            SELECT * FROM user_addresses 
            WHERE user_id = ? 
            ORDER BY is_default DESC, updated_at DESC
        ");
        $stmt->execute([$userId]);
        return $stmt->fetchAll(\PDO::FETCH_ASSOC) ?: [];
    }

    public function saveOrUpdate(int $userId, array $data): int {
        $existing = $this->getDefaultAddress($userId);
        
        $fullName     = trim((string)($data['full_name'] ?? $data['name'] ?? ''));
        $phone        = trim((string)($data['phone'] ?? ''));
        $email        = trim((string)($data['email'] ?? ''));
        $addressLine1 = trim((string)($data['address_line1'] ?? $data['address1'] ?? ''));
        $addressLine2 = trim((string)($data['address_line2'] ?? $data['address2'] ?? ''));
        $landmark     = trim((string)($data['landmark'] ?? ''));
        $city         = trim((string)($data['city'] ?? ''));
        $state        = trim((string)($data['state'] ?? ''));
        $pincode      = trim((string)($data['pincode'] ?? ''));
        $country      = trim((string)($data['country'] ?? 'India'));
        if (empty($country)) {
            $country = 'India';
        }

        if ($existing) {
            $stmt = $this->db->prepare("
                UPDATE user_addresses SET 
                    full_name = ?,
                    phone = ?,
                    email = ?,
                    address_line1 = ?,
                    address_line2 = ?,
                    landmark = ?,
                    city = ?,
                    state = ?,
                    pincode = ?,
                    country = ?,
                    is_default = 1,
                    updated_at = NOW()
                WHERE id = ? AND user_id = ?
            ");
            $stmt->execute([
                $fullName,
                $phone,
                $email,
                $addressLine1,
                $addressLine2,
                $landmark,
                $city,
                $state,
                $pincode,
                $country,
                $existing['id'],
                $userId
            ]);
            return (int)$existing['id'];
        } else {
            $stmt = $this->db->prepare("
                INSERT INTO user_addresses (
                    user_id, full_name, phone, email, address_line1, address_line2, landmark, city, state, pincode, country, is_default
                ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, 1)
            ");
            $stmt->execute([
                $userId,
                $fullName,
                $phone,
                $email,
                $addressLine1,
                $addressLine2,
                $landmark,
                $city,
                $state,
                $pincode,
                $country
            ]);
            return (int)$this->db->lastInsertId();
        }
    }
}
