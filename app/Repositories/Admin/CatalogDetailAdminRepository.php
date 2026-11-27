<?php
declare(strict_types=1);
namespace App\Repositories\Admin;
use PDO;
final class CatalogDetailAdminRepository {
    public function __construct(private PDO $db) {}
    private function run(string $sql,array $args=[]): \PDOStatement { $s=$this->db->prepare($sql); $s->execute($args); return $s; }
    public function product(int $id,bool $lock=false): ?array { return $this->run('SELECT id,name,status FROM products WHERE id=? AND deleted_at IS NULL'.($lock?' FOR UPDATE':''),[$id])->fetch(PDO::FETCH_ASSOC) ?: null; }
    public function configurations(int $id): array {return $this->run('SELECT * FROM product_configurations WHERE product_id=? AND deleted_at IS NULL ORDER BY storage_gb,ram_gb',[$id])->fetchAll(PDO::FETCH_ASSOC);}
    public function variants(int $id): array {return $this->run('SELECT v.*,c.name AS color_name FROM product_variants v JOIN product_configurations f ON f.id=v.configuration_id JOIN colors c ON c.id=v.color_id WHERE f.product_id=? AND f.deleted_at IS NULL AND v.deleted_at IS NULL ORDER BY v.id',[$id])->fetchAll(PDO::FETCH_ASSOC);}
    public function colors(): array {return $this->run('SELECT id,name FROM colors WHERE is_active=1 AND deleted_at IS NULL ORDER BY name')->fetchAll(PDO::FETCH_ASSOC);}
    public function images(int $id): array {return $this->run('SELECT id,alt_text,sort_order FROM product_images WHERE product_id=? AND deleted_at IS NULL ORDER BY sort_order,id',[$id])->fetchAll(PDO::FETCH_ASSOC);}
    public function image(int $product,int $image): ?array {return $this->run('SELECT storage_key FROM product_images WHERE id=? AND product_id=? AND deleted_at IS NULL',[$image,$product])->fetch(PDO::FETCH_ASSOC) ?: null;}
    public function transaction(int $id,callable $fn): void { $this->db->beginTransaction(); try { $p=$this->product($id,true); if(!$p) throw new \OutOfBoundsException('Không tìm thấy sản phẩm.'); $fn($p); $this->db->commit(); } catch(\Throwable $e){if($this->db->inTransaction())$this->db->rollBack();throw $e;} }
    public function saveConfig(int $product,int $id,array $v,string $uuid): void {
        if($id===0){$this->run('INSERT INTO product_configurations(public_id,product_id,storage_gb,ram_gb,list_price,sale_price,sale_starts_at,sale_ends_at,is_active) VALUES (?,?,?,?,?,?,?,?,?)',[$uuid,$product,...array_values($v)]);return;}
        if(!$this->run('SELECT id FROM product_configurations WHERE id=? AND product_id=? AND deleted_at IS NULL',[$id,$product])->fetchColumn())throw new \OutOfBoundsException('Không tìm thấy cấu hình.');
        $this->run('UPDATE product_configurations SET storage_gb=?,ram_gb=?,list_price=?,sale_price=?,sale_starts_at=?,sale_ends_at=?,is_active=?,updated_at=NOW(6) WHERE id=? AND product_id=? AND deleted_at IS NULL',[...array_values($v),$id,$product]);
    }
    public function saveVariant(int $product,int $id,array $v,string $uuid): void {
        if(!$this->run('SELECT id FROM product_configurations WHERE id=? AND product_id=? AND deleted_at IS NULL',[$v['configuration_id'],$product])->fetchColumn())throw new \InvalidArgumentException('Cấu hình không thuộc sản phẩm.');
        if(!$this->run('SELECT id FROM colors WHERE id=? AND is_active=1 AND deleted_at IS NULL',[$v['color_id']])->fetchColumn())throw new \InvalidArgumentException('Màu không hợp lệ.');
        if($id===0){$this->run('INSERT INTO product_variants(public_id,configuration_id,color_id,sku,is_active) VALUES (?,?,?,?,?)',[$uuid,...array_values($v)]);return;}
        if(!$this->run('SELECT v.id FROM product_variants v JOIN product_configurations c ON c.id=v.configuration_id WHERE v.id=? AND c.product_id=? AND v.deleted_at IS NULL AND c.deleted_at IS NULL',[$id,$product])->fetchColumn())throw new \OutOfBoundsException('Không tìm thấy biến thể.');
        $this->run('UPDATE product_variants SET configuration_id=?,color_id=?,sku=?,is_active=?,updated_at=NOW(6) WHERE id=? AND deleted_at IS NULL',[...array_values($v),$id]);
    }
    public function addImage(int $product,string $key,string $alt,int $sort): void {$this->run('INSERT INTO product_images(product_id,storage_key,alt_text,sort_order) VALUES (?,?,?,?)',[$product,$key,$alt,$sort]);}
    public function removeImage(int $product,int $image): void {if($this->run('UPDATE product_images SET deleted_at=NOW(6),updated_at=NOW(6) WHERE product_id=? AND id=? AND deleted_at IS NULL',[$product,$image])->rowCount()!==1)throw new \OutOfBoundsException('Không tìm thấy ảnh.');}
    public function ready(int $product): bool {return (bool)$this->run('SELECT 1 FROM product_configurations c JOIN product_variants v ON v.configuration_id=c.id JOIN colors k ON k.id=v.color_id WHERE c.product_id=? AND c.deleted_at IS NULL AND c.is_active=1 AND c.list_price>0 AND v.deleted_at IS NULL AND v.is_active=1 AND k.deleted_at IS NULL AND k.is_active=1 LIMIT 1',[$product])->fetchColumn();}
}
