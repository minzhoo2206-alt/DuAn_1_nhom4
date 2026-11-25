<?php
declare(strict_types=1);
namespace App\Repositories\Admin;

use App\Core\Database;
use PDO;

/** Chỉ sử dụng tên bảng/cột đã whitelist; không nội suy input vào SQL. */
class CatalogAdminRepository
{
    private const TABLES = ['products' => 'products', 'categories' => 'categories', 'brands' => 'brands'];

    public function __construct(protected ?PDO $db = null)
    {
        $this->db ??= Database::connection();
    }

    protected function table(string $kind): string
    {
        return self::TABLES[$kind] ?? throw new \InvalidArgumentException('Loại dữ liệu không được hỗ trợ.');
    }

    public function paginate(string $kind, string $search, int $limit, int $offset): array
    {
        $table = $this->table($kind);
        $sql = "SELECT id, name, slug, status FROM {$table} WHERE 1=1";
        if ($search !== '') { $sql .= ' AND (name LIKE :search OR slug LIKE :search)'; }
        $sql .= ' ORDER BY id DESC LIMIT :limit OFFSET :offset';
        $stmt = $this->db->prepare($sql);
        if ($search !== '') { $stmt->bindValue(':search', '%'.$search.'%'); }
        $stmt->bindValue(':limit', $limit, PDO::PARAM_INT);
        $stmt->bindValue(':offset', $offset, PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function count(string $kind, string $search = ''): int
    {
        $table = $this->table($kind);
        $sql = "SELECT COUNT(id) FROM {$table} WHERE 1=1";
        if ($search !== '') { $sql .= ' AND (name LIKE :search OR slug LIKE :search)'; }
        $stmt = $this->db->prepare($sql);
        $stmt->execute($search === '' ? [] : ['search' => '%'.$search.'%']);
        return (int) $stmt->fetchColumn();
    }

    public function find(string $kind, int $id): ?array
    {
        $table = $this->table($kind);
        $stmt = $this->db->prepare("SELECT id, name, slug, status FROM {$table} WHERE id=:id LIMIT 1");
        $stmt->execute(['id' => $id]);
        return $stmt->fetch(PDO::FETCH_ASSOC) ?: null;
    }

    public function slugExists(string $kind, string $slug, ?int $exceptId = null): bool
    {
        $table = $this->table($kind);
        $sql = "SELECT id FROM {$table} WHERE slug=:slug";
        if ($exceptId !== null) { $sql .= ' AND id<>:except_id'; }
        $sql .= ' LIMIT 1';
        $stmt = $this->db->prepare($sql);
        $stmt->execute($exceptId === null ? ['slug' => $slug] : ['slug' => $slug, 'except_id' => $exceptId]);
        return $stmt->fetchColumn() !== false;
    }

    public function create(string $kind, array $data): int
    {
        $table = $this->table($kind);
        $stmt = $this->db->prepare("INSERT INTO {$table} (name, slug, status) VALUES (:name, :slug, :status)");
        $stmt->execute($data);
        return (int) $this->db->lastInsertId();
    }

    public function update(string $kind, int $id, array $data): void
    {
        $table = $this->table($kind);
        $stmt = $this->db->prepare("UPDATE {$table} SET name=:name, slug=:slug, status=:status WHERE id=:id");
        $stmt->execute($data + ['id' => $id]);
    }

    public function setStatus(string $kind, int $id, string $status): void
    {
        $table = $this->table($kind);
        $stmt = $this->db->prepare("UPDATE {$table} SET status=:status WHERE id=:id");
        $stmt->execute(['status' => $status, 'id' => $id]);
    }
}
