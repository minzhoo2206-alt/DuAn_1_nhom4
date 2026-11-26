<?php

declare(strict_types=1);

namespace App\Repositories\Admin;

use PDO;

final class ProductAdminRepository
{
    public function __construct(private PDO $db)
    {
    }

    public function paginate(int $limit = 20, int $offset = 0): array
    {
        $sql = 'SELECT p.id, p.name, p.slug, p.status,
                       p.created_at, b.name AS brand_name,
                       c.name AS category_name
                FROM products AS p
                LEFT JOIN brands AS b ON b.id = p.brand_id
                LEFT JOIN categories AS c ON c.id = p.category_id
                WHERE p.deleted_at IS NULL
                ORDER BY p.id DESC
                LIMIT :limit OFFSET :offset';

        $stmt = $this->db->prepare($sql);
        $stmt->bindValue(':limit', $limit, PDO::PARAM_INT);
        $stmt->bindValue(':offset', $offset, PDO::PARAM_INT);
        $stmt->execute();

        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function find(int $id): ?array
    {
        $sql = 'SELECT id, name, slug, brand_id, category_id,
                       description, status, warranty_months
                FROM products
                WHERE id = :id AND deleted_at IS NULL
                LIMIT 1';

        $stmt = $this->db->prepare($sql);
        $stmt->bindValue(':id', $id, PDO::PARAM_INT);
        $stmt->execute();

        $product = $stmt->fetch(PDO::FETCH_ASSOC);

        return $product === false ? null : $product;
    }

    public function count(): int
    {
        $sql = 'SELECT COUNT(*) FROM products
                WHERE deleted_at IS NULL';

        $stmt = $this->db->prepare($sql);
        $stmt->execute();

        return (int) $stmt->fetchColumn();
    }

    // Danh sách thương hiệu và danh mục đang hoạt động.
    public function brands(): array
    {
        return $this->db->query(
            'SELECT id, name FROM brands WHERE is_active = 1 AND deleted_at IS NULL ORDER BY name'
        )->fetchAll(PDO::FETCH_ASSOC);
    }

    public function categories(): array
    {
        return $this->db->query(
            'SELECT id, name FROM categories WHERE is_active = 1 AND deleted_at IS NULL ORDER BY name'
        )->fetchAll(PDO::FETCH_ASSOC);
    }

    public function slugExists(string $slug): bool
    {
        $stmt = $this->db->prepare('SELECT id FROM products WHERE slug = :slug LIMIT 1');
        $stmt->execute(['slug' => $slug]);
        return $stmt->fetchColumn() !== false;
    }

    public function create(array $data): int
    {
        $sql = 'INSERT INTO products
                (public_id, name, slug, brand_id, category_id, description,
                 warranty_months, status, created_at, updated_at)
                VALUES (:public_id, :name, :slug, :brand_id, :category_id,
                        :description, :warranty_months, :status, NOW(), NOW())';
        $stmt = $this->db->prepare($sql);
        $stmt->execute($data);
        return (int) $this->db->lastInsertId();
    }


    public function slugExistsForOther(string $slug, int $id): bool
    {
        $stmt = $this->db->prepare('SELECT id FROM products WHERE slug = :slug AND id <> :id LIMIT 1');
        $stmt->execute(['slug' => $slug, 'id' => $id]);
        return $stmt->fetchColumn() !== false;
    }

    public function update(int $id, array $data): void
    {
        $sql = 'UPDATE products SET name = :name, slug = :slug,
                brand_id = :brand_id, category_id = :category_id,
                description = :description, warranty_months = :warranty_months,
                updated_at = NOW()
                WHERE id = :id AND deleted_at IS NULL';
        $stmt = $this->db->prepare($sql);
        $stmt->execute($data + ['id' => $id]);
        if ($stmt->rowCount() === 0 && $this->find($id) === null) {
            throw new \OutOfBoundsException('Không tìm thấy sản phẩm.');
        }
    }

}
