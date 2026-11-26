<?php

declare(strict_types=1);
namespace App\Repositories\Admin;
use PDO;

final class CategoryAdminRepository
{
    public function __construct(private PDO $db) {}
    public function all(): array
    {
        return $this->db->query('SELECT id, name, slug, is_active FROM categories WHERE deleted_at IS NULL ORDER BY id DESC')->fetchAll(PDO::FETCH_ASSOC);
    }
    public function find(int $id): ?array
    {
        $stmt = $this->db->prepare('SELECT id, name, slug, is_active FROM categories WHERE id = :id AND deleted_at IS NULL');
        $stmt->execute(['id'=>$id]);
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        return $row === false ? null : $row;
    }
    public function slugExists(string $slug, int $except = 0): bool
    {
        $stmt = $this->db->prepare('SELECT 1 FROM categories WHERE slug = :slug AND id <> :id LIMIT 1');
        $stmt->execute(['slug'=>$slug, 'id'=>$except]);
        return $stmt->fetchColumn() !== false;
    }
    public function create(array $data): int
    {
        $stmt = $this->db->prepare('INSERT INTO categories (name, slug, is_active, created_at, updated_at) VALUES (:name, :slug, 1, NOW(6), NOW(6))');
        $stmt->execute($data);
        return (int) $this->db->lastInsertId();
    }
    public function update(int $id, array $data): void
    {
        $stmt = $this->db->prepare('UPDATE categories SET name = :name, slug = :slug, updated_at = NOW(6) WHERE id = :id AND deleted_at IS NULL');
        $stmt->execute($data + ['id'=>$id]);
        if ($stmt->rowCount() === 0 && $this->find($id) === null) throw new \OutOfBoundsException('Không tìm thấy danh mục.');
    }
    public function hasProducts(int $id): bool
    {
        $stmt = $this->db->prepare('SELECT 1 FROM products WHERE category_id = :id AND deleted_at IS NULL LIMIT 1');
        $stmt->execute(['id'=>$id]);
        return $stmt->fetchColumn() !== false;
    }
    public function lifecycle(int $id, string $action): bool
    {
        $sql = match ($action) {
            'activate' => 'UPDATE categories SET is_active = 1, updated_at = NOW(6) WHERE id = :id AND deleted_at IS NULL AND is_active = 0',
            'deactivate' => 'UPDATE categories SET is_active = 0, updated_at = NOW(6) WHERE id = :id AND deleted_at IS NULL AND is_active = 1 AND NOT EXISTS (SELECT 1 FROM products WHERE category_id = categories.id AND deleted_at IS NULL)',
            'delete' => 'UPDATE categories SET deleted_at = NOW(6), updated_at = NOW(6) WHERE id = :id AND deleted_at IS NULL AND is_active = 0 AND NOT EXISTS (SELECT 1 FROM products WHERE category_id = categories.id AND deleted_at IS NULL)',
            default => throw new \InvalidArgumentException('Thao tác không hợp lệ.'),
        };
        $stmt = $this->db->prepare($sql);
        $stmt->execute(['id'=>$id]);
        return $stmt->rowCount() === 1;
    }
}
