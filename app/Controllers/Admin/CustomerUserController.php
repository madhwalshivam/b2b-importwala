<?php

namespace App\Controllers\Admin;

use App\Core\Controller;
use App\Core\Database;
use App\Services\AuthSchema;

class CustomerUserController extends Controller
{
    public function index(): string
    {
        AuthSchema::ensure();
        $db = Database::getInstance();
        $q = trim((string)($_GET['q'] ?? ''));
        $filter = (string)($_GET['filter'] ?? '');
        $page = max(1, (int)($_GET['page'] ?? 1));
        $perPage = 40;

        [$where, $params] = $this->filters($q, $filter);
        $countStmt = $db->prepare("SELECT COUNT(*) FROM users WHERE {$where}");
        $countStmt->execute($params);
        $total = (int)$countStmt->fetchColumn();

        $offset = ($page - 1) * $perPage;
        $stmt = $db->prepare("SELECT * FROM users WHERE {$where} ORDER BY id DESC LIMIT {$perPage} OFFSET {$offset}");
        $stmt->execute($params);
        $users = array_map([$this, 'present'], $stmt->fetchAll(\PDO::FETCH_ASSOC));

        return $this->render('admin/customers/index', [
            'users' => $users,
            'q' => $q,
            'filter' => $filter,
            'page' => $page,
            'pages' => max(1, (int)ceil($total / $perPage)),
            'total' => $total,
        ]);
    }

    public function exportCsv(): void
    {
        AuthSchema::ensure();
        $db = Database::getInstance();
        $q = trim((string)($_GET['q'] ?? ''));
        $filter = (string)($_GET['filter'] ?? '');
        [$where, $params] = $this->filters($q, $filter);
        $stmt = $db->prepare("SELECT * FROM users WHERE {$where} ORDER BY id DESC");
        $stmt->execute($params);
        $rows = $stmt->fetchAll(\PDO::FETCH_ASSOC);

        header('Content-Type: text/csv; charset=utf-8');
        header('Content-Disposition: attachment; filename="importwale-customers.csv"');
        $out = fopen('php://output', 'w');
        fputcsv($out, ['name', 'email', 'phone', 'gst_number', 'source', 'created_at']);
        foreach ($rows as $row) {
            $user = $this->present($row);
            fputcsv($out, [
                $user['name'],
                $user['email'],
                $user['phone'],
                $user['gst_number'] ?? '',
                $user['signup_source'] ?: $user['auth_provider'],
                $user['created_at'] ?? '',
            ]);
        }
        fclose($out);
        exit;
    }

    private function filters(string $q, string $filter): array
    {
        $db = Database::getInstance();
        $cols = AuthSchema::columns($db, 'users');
        $where = ['1=1'];
        $params = [];
        if ($q !== '') {
            $parts = [];
            foreach (['email', 'phone', 'name', 'company_name', 'gst_number', 'first_name', 'last_name'] as $col) {
                if (isset($cols[$col])) {
                    $parts[] = "{$col} LIKE ?";
                    $params[] = '%' . $q . '%';
                }
            }
            if ($parts) {
                $where[] = '(' . implode(' OR ', $parts) . ')';
            }
        }
        if ($filter === 'incomplete' && isset($cols['profile_completed'])) {
            $where[] = 'profile_completed = 0';
        } elseif ($filter === 'google' && isset($cols['auth_provider'])) {
            $where[] = "auth_provider IN ('google','both')";
        } elseif ($filter === 'phone_unverified' && isset($cols['phone_verified'])) {
            $where[] = '(phone_verified = 0 OR phone_verified IS NULL)';
        }
        return [implode(' AND ', $where), $params];
    }

    private function present(array $row): array
    {
        if (empty($row['name'])) {
            $row['name'] = trim((string)($row['first_name'] ?? '') . ' ' . (string)($row['last_name'] ?? ''));
        }
        $row['phone'] = $row['phone'] ?? '';
        $row['signup_source'] = $row['signup_source'] ?? ($row['auth_provider'] ?? 'email');
        $row['auth_provider'] = $row['auth_provider'] ?? 'email';
        $row['phone_verified'] = (int)($row['phone_verified'] ?? 0);
        $row['profile_completed'] = (int)($row['profile_completed'] ?? 0);
        return $row;
    }
}
