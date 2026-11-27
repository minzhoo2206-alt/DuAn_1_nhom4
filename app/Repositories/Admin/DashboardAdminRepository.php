<?php
declare(strict_types=1);
namespace App\Repositories\Admin;
use App\Services\Admin\RbacAdminService as Rbac;
use PDO;
use DomainException;

final class DashboardAdminRepository
{
    private const ORDERS = "deleted_at IS NULL AND (checkout_key LIKE 'task18-demo-%' OR checkout_key LIKE 'task19-demo-%')";
    public function __construct(private PDO $db)
    {
        if ($db->query('SELECT DATABASE()')->fetchColumn() !== 'fourtech_admin_test') throw new DomainException('Local only');
    }
    public function summary(array $actor): array
    {
        if (!Rbac::allowed($actor, Rbac::READ)) throw new DomainException('Không đủ quyền.');
        $stats = [];
        foreach (['products'=>'Sản phẩm chưa xóa','categories'=>'Danh mục chưa xóa','brands'=>'Thương hiệu chưa xóa'] as $table=>$label) {
            $stats[$label] = (string)$this->db->query("SELECT COUNT(*) FROM `$table` WHERE deleted_at IS NULL")->fetchColumn();
        }
        if (Rbac::allowed($actor, Rbac::USERS)) {
            $stats['Tài khoản chưa xóa'] = (string)$this->db->query('SELECT COUNT(*) FROM users WHERE deleted_at IS NULL')->fetchColumn();
        }
        $canOrders = Rbac::allowed($actor, Rbac::ORDERS);
        $orders = [];
        if ($canOrders) {
            $row = $this->db->query("SELECT COUNT(*) AS total, COALESCE(SUM(status='pending_confirmation'),0) AS pending, COALESCE(SUM(status='delivered'),0) AS delivered, COALESCE(SUM(CASE WHEN status='delivered' THEN cod_total ELSE 0 END),0) AS delivered_cod FROM orders WHERE ".self::ORDERS)->fetch(PDO::FETCH_ASSOC);
            $stats['Đơn hàng mẫu'] = (string)$row['total'];
            $stats['Chờ xác nhận'] = (string)$row['pending'];
            $stats['Giao thành công'] = (string)$row['delivered'];
            $stats['COD đơn mẫu đã giao'] = number_format((float)$row['delivered_cod'],0,',','.') . ' ₫';
            $orders = $this->db->query('SELECT id,checkout_key,status,cod_total,created_at FROM orders WHERE '.self::ORDERS.' ORDER BY created_at DESC,id DESC LIMIT 5')->fetchAll(PDO::FETCH_ASSOC);
        }
        return compact('stats','orders','canOrders');
    }
}
