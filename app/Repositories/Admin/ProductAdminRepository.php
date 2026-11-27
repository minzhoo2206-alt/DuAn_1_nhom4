<?php

declare(strict_types=1);

namespace App\Repositories\Admin;

use PDO;

final class ProductAdminRepository
{
    public function __construct(private PDO $db)
    {
    }

    // The same predicate is used for COUNT and pagination. User values are bound.
    public static function filterPredicate(array $filters): array
    {
        $conditions = ['p.deleted_at IS NULL'];
        $params = [];
        if (($filters['q'] ?? '') !== '') {
            $conditions[] = "(p.name LIKE :name_q ESCAPE '!' OR p.slug LIKE :slug_q ESCAPE '!')";
            $term = '%' . strtr($filters['q'], ['!' => '!!', '%' => '!%', '_' => '!_']) . '%';
            $params[':name_q'] = $term;
            $params[':slug_q'] = $term;
        }
        foreach (['brand_id', 'category_id', 'status'] as $field) {
            if (!empty($filters[$field])) {
                $conditions[] = 'p.' . $field . ' = :' . $field;
                $params[':' . $field] = $filters[$field];
            }
        }
        return [implode(' AND ', $conditions), $params];
    }

    private function bindFilters(\PDOStatement $stmt, array $params): void
    {
        foreach ($params as $key => $value) {
            $stmt->bindValue($key, $value, is_int($value) ? PDO::PARAM_INT : PDO::PARAM_STR);
        }
    }

    public function paginate(int $limit = 20, int $offset = 0, array $filters = []): array
    {
        [$where, $params] = self::filterPredicate($filters);
        $sql = 'SELECT p.id, p.name, p.slug, p.status,
                       p.created_at, b.name AS brand_name,
                       c.name AS category_name
                FROM products AS p
                LEFT JOIN brands AS b ON b.id = p.brand_id
                LEFT JOIN categories AS c ON c.id = p.category_id
                WHERE ' . $where . '
                ORDER BY p.id DESC
                LIMIT :limit OFFSET :offset';
        $stmt = $this->db->prepare($sql);
        $this->bindFilters($stmt, $params);
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

    public function count(array $filters = []): int
    {
        [$where, $params] = self::filterPredicate($filters);
        $stmt = $this->db->prepare('SELECT COUNT(*) FROM products AS p WHERE ' . $where);
        $this->bindFilters($stmt, $params);
        $stmt->execute();
        return (int) $stmt->fetchColumn();
    }

    public function filterOptions(): array
    {
        return [
            'brands' => $this->db->query('SELECT id, name FROM brands WHERE deleted_at IS NULL ORDER BY name')->fetchAll(PDO::FETCH_ASSOC),
            'categories' => $this->db->query('SELECT id, name FROM categories WHERE deleted_at IS NULL ORDER BY name')->fetchAll(PDO::FETCH_ASSOC),
        ];
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


    // Local catalog activation: require price and active variant; not a stock guarantee.
    public function activate(int $id): bool
    {
        $stmt = $this->db->prepare("UPDATE products AS p
            INNER JOIN brands AS b ON b.id = p.brand_id
            INNER JOIN categories AS c ON c.id = p.category_id
            SET p.status = 'active', p.updated_at = NOW(6)
            WHERE p.id = :id AND p.deleted_at IS NULL
            AND p.status IN ('draft', 'hidden')
            AND b.is_active = 1 AND b.deleted_at IS NULL
            AND c.is_active = 1 AND c.deleted_at IS NULL
            AND EXISTS (SELECT 1 FROM product_configurations f
                JOIN product_variants v ON v.configuration_id=f.id
                JOIN colors k ON k.id=v.color_id
                WHERE f.product_id=p.id AND f.deleted_at IS NULL AND f.is_active=1 AND f.list_price>0
                AND v.deleted_at IS NULL AND v.is_active=1 AND k.deleted_at IS NULL AND k.is_active=1)");
        $stmt->execute(['id' => $id]);
        return $stmt->rowCount() === 1;
    }

    // Chỉ ẩn sản phẩm đang bán, không thay đổi tồn kho.
    public function hide(int $id): bool
    {
        $stmt = $this->db->prepare("UPDATE products
            SET status = 'hidden', updated_at = NOW(6)
            WHERE id = :id AND deleted_at IS NULL AND status = 'active'");
        $stmt->execute(['id' => $id]);
        return $stmt->rowCount() === 1;
    }

    // Giữ bản ghi để không phá liên kết với dữ liệu lịch sử.
    public function softDelete(int $id): bool
    {
        $stmt = $this->db->prepare("UPDATE products
            SET deleted_at = NOW(6), updated_at = NOW(6)
            WHERE id = :id AND deleted_at IS NULL
            AND status IN ('draft', 'hidden')");
        $stmt->execute(['id' => $id]);
        return $stmt->rowCount() === 1;
    }

}
