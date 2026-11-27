<?php
declare(strict_types=1);
namespace App\Repositories\Admin;
use App\Services\Admin\OrderAdminService as Rules;
use App\Services\Admin\RbacAdminService as Rbac;
use PDO;
use DomainException;
use OutOfBoundsException;
use Throwable;
final class OrderAdminRepository
{
    private const SCOPE="(checkout_key LIKE 'task18-demo-%' OR checkout_key LIKE 'task19-demo-%') AND deleted_at IS NULL";
    public function __construct(private PDO $db){if($db->query('SELECT DATABASE()')->fetchColumn()!=='fourtech_admin_test')throw new DomainException('Local only');}
    private function q(string $sql,array $args=[]): \PDOStatement {$s=$this->db->prepare($sql);$s->execute($args);return $s;}
    public function listing(string $search,string $status,int $page): array
    {
        $where="(o.checkout_key LIKE 'task18-demo-%' OR o.checkout_key LIKE 'task19-demo-%') AND o.deleted_at IS NULL";$args=[];
        if($search!==''){$where.=' AND (o.checkout_key LIKE ? OR u.email LIKE ?)';$args[]='%'.$search.'%';$args[]='%'.$search.'%';}
        if(isset(Rules::LABELS[$status])){$where.=' AND o.status=?';$args[]=$status;}
        $base=' FROM orders o LEFT JOIN users u ON u.id=o.user_id WHERE '.$where;
        $total=(int)$this->q('SELECT COUNT(*)'.$base,$args)->fetchColumn();$pages=max(1,(int)ceil($total/10));$page=max(1,min($page,$pages));$offset=($page-1)*10;
        $orders=$this->q('SELECT o.id,o.checkout_key,o.status,o.items_total,o.tax_total,o.shipping_fee,o.cod_total,o.currency,o.created_at,u.email'.$base.' ORDER BY o.id DESC LIMIT 10 OFFSET '.$offset,$args)->fetchAll(PDO::FETCH_ASSOC);
        return compact('orders','total','pages','page');
    }
    public function detail(int $id): array
    {
        $order=$this->q("SELECT o.id,o.checkout_key,o.status,o.items_total,o.tax_total,o.shipping_fee,o.cod_total,o.currency,o.created_at,u.email FROM orders o LEFT JOIN users u ON u.id=o.user_id WHERE o.id=? AND (o.checkout_key LIKE 'task18-demo-%' OR o.checkout_key LIKE 'task19-demo-%') AND o.deleted_at IS NULL",[$id])->fetch(PDO::FETCH_ASSOC);
        if(!$order)throw new OutOfBoundsException('Không tìm thấy đơn mẫu.');
        $items=$this->q('SELECT product_name,sku,quantity,unit_price,line_tax,line_total FROM order_items WHERE order_id=? AND deleted_at IS NULL ORDER BY id',[$id])->fetchAll(PDO::FETCH_ASSOC);
        $history=$this->q('SELECT from_status,to_status,reason,created_at,created_by FROM order_status_history WHERE order_id=? AND deleted_at IS NULL ORDER BY id DESC',[$id])->fetchAll(PDO::FETCH_ASSOC);
        return compact('order','items','history');
    }
    public function transition(int $id,int $actorId,string $expected,string $to,string $reason): void
    {
        $this->db->beginTransaction();try{
            $this->q('SELECT id FROM users WHERE id=? FOR UPDATE',[$actorId]);
            if(!Rbac::allowed((new RbacAdminRepository($this->db))->actor($actorId),Rbac::ORDERS))throw new DomainException('Không đủ quyền.');
            $from=$this->q('SELECT status FROM orders WHERE id=? AND '.self::SCOPE.' FOR UPDATE',[$id])->fetchColumn();
            if($from===false)throw new OutOfBoundsException('Không tìm thấy đơn mẫu.');
            if($from!==$expected)throw new DomainException('Trạng thái đã thay đổi. Hãy tải lại trang.');
            Rules::validate($from,$to,$reason);
            $this->q("UPDATE orders SET status=?,updated_at=NOW(6),updated_by=?,confirmed_at=CASE WHEN ?='confirmed' THEN NOW(6) ELSE confirmed_at END, delivered_at=CASE WHEN ?='delivered' THEN NOW(6) ELSE delivered_at END,cancelled_at=CASE WHEN ?='cancelled' THEN NOW(6) ELSE cancelled_at END,cancellation_reason=CASE WHEN ?='cancelled' THEN ? ELSE cancellation_reason END WHERE id=?",[$to,$actorId,$to,$to,$to,$to,$reason,$id]);
            $this->q('INSERT INTO order_status_history(order_id,from_status,to_status,reason,created_by,updated_by) VALUES(?,?,?,?,?,?)',[$id,$from,$to,$reason,$actorId,$actorId]);
            $this->db->commit();
        }catch(Throwable $e){if($this->db->inTransaction())$this->db->rollBack();throw $e;}
    }
}
