<?php
declare(strict_types=1);
namespace App\Repositories\Admin;

use App\Core\Database;
use PDO;

/** Chỉ đọc các cột tối thiểu theo hợp đồng, không chỉnh trạng thái ở đây. */
class OrderAdminRepository
{
    public function __construct(protected ?PDO $db = null) { $this->db ??= Database::connection(); }

    public function paginate(?string $status, int $limit, int $offset): array
    {
        $sql = 'SELECT id, status, created_at FROM orders';
        if ($status !== null) { $sql .= ' WHERE status=:status'; }
        $sql .= ' ORDER BY id DESC LIMIT :limit OFFSET :offset';
        $stmt = $this->db->prepare($sql);
        if ($status !== null) { $stmt->bindValue('status', $status); }
        $stmt->bindValue('limit', $limit, PDO::PARAM_INT);
        $stmt->bindValue('offset', $offset, PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function count(?string $status): int
    {
        $sql = 'SELECT COUNT(id) FROM orders';
        if ($status !== null) { $sql .= ' WHERE status=:status'; }
        $stmt = $this->db->prepare($sql);
        $stmt->execute($status === null ? [] : ['status'=>$status]);
        return (int) $stmt->fetchColumn();
    }

    public function find(int $id): ?array
    {
        $stmt = $this->db->prepare('SELECT id, status, created_at FROM orders WHERE id=:id LIMIT 1');
        $stmt->execute(['id'=>$id]);
        return $stmt->fetch(PDO::FETCH_ASSOC) ?: null;
    }
}
