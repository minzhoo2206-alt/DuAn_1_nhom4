<?php
declare(strict_types=1);
namespace App\Repositories\Admin;
use PDO;
use RuntimeException;
use DomainException;
use OutOfBoundsException;
use Throwable;

// Test-only until official auth, audit, crypto and role contract are integrated.
final class UserAdminRepository
{
    public function __construct(private PDO $db)
    {
        if ($db->query('SELECT DATABASE()')->fetchColumn() !== 'fourtech_admin_test') {
            throw new RuntimeException('Task 16 only supports the local test schema.');
        }
    }
    private const FIELDS = 'u.id,u.email,u.status,u.created_at,u.last_login_at';
    private const ROLES = "(SELECT GROUP_CONCAT(r.code ORDER BY r.code SEPARATOR ',') FROM user_has_roles ur JOIN roles r ON r.id=ur.role_id WHERE ur.user_id=u.id AND ur.deleted_at IS NULL AND r.deleted_at IS NULL) AS role_codes";
    public function listing(string $q, string $status, int $page): array
    {
        $where = 'u.deleted_at IS NULL'; $args = [];
        if ($q !== '') { $where .= ' AND LOCATE(:q,u.email)>0'; $args['q']=$q; }
        if ($status !== '') { $where .= ' AND u.status=:status'; $args['status']=$status; }
        $count=$this->db->prepare('SELECT COUNT(*) FROM users u WHERE '.$where); $count->execute($args);
        $total=(int)$count->fetchColumn(); $pages=max(1,(int)ceil($total/10)); $page=min(max(1,$page),$pages);
        $offset=($page-1)*10;
        $stmt=$this->db->prepare('SELECT '.self::FIELDS.','.self::ROLES.' FROM users u WHERE '.$where.' ORDER BY u.id DESC LIMIT 10 OFFSET '.$offset);
        $stmt->execute($args);
        return ['users'=>$stmt->fetchAll(PDO::FETCH_ASSOC),'total'=>$total,'page'=>$page,'pages'=>$pages];
    }
    public function find(int $id): ?array
    {
        $s=$this->db->prepare('SELECT '.self::FIELDS.','.self::ROLES.' FROM users u WHERE u.id=:id AND u.deleted_at IS NULL'); $s->execute(['id'=>$id]);
        return $s->fetch(PDO::FETCH_ASSOC) ?: null;
    }
    private function atomic(callable $fn): mixed
    {
        $this->db->beginTransaction();
        try { $result=$fn(); $this->db->commit(); return $result; }
        catch(Throwable $e) { if($this->db->inTransaction()) $this->db->rollBack(); throw $e; }
    }
    public function create(string $email,string $hash,string $uuid): int
    {
        return $this->atomic(function() use($email,$hash,$uuid): int {
            $role=$this->db->query("SELECT id FROM roles WHERE code='task16_staff' AND deleted_at IS NULL FOR UPDATE")->fetchColumn();
            if(!$role) throw new DomainException('Thiếu role kiểm thử. Chạy SQL Task 16.');
            $s=$this->db->prepare("INSERT INTO users(public_id,email,password_hash,status) VALUES(:uuid,:email,:hash,'active')");
            $s->execute(compact('uuid','email','hash')); $id=(int)$this->db->lastInsertId();
            $s=$this->db->prepare('INSERT INTO user_has_roles(user_id,role_id) VALUES(:id,:role)'); $s->execute(compact('id','role'));
            return $id;
        });
    }
    public function mutate(int $id,string $action,?string $value=null): void
    {
        $this->atomic(function() use($id,$action,$value): void {
            $s=$this->db->prepare('SELECT id,status FROM users WHERE id=:id AND deleted_at IS NULL FOR UPDATE'); $s->execute(compact('id')); $row=$s->fetch(PDO::FETCH_ASSOC);
            if(!$row) throw new OutOfBoundsException('Không tìm thấy tài khoản.');
            $s=$this->db->prepare('SELECT r.code,r.deleted_at FROM user_has_roles ur JOIN roles r ON r.id=ur.role_id WHERE ur.user_id=:id AND ur.deleted_at IS NULL FOR UPDATE'); $s->execute(compact('id')); $roles=$s->fetchAll(PDO::FETCH_ASSOC);
            if(count($roles)!==1 || $roles[0]['code']!=='task16_staff' || $roles[0]['deleted_at']!==null) throw new DomainException('Tài khoản được bảo vệ; chỉ sửa nhân viên kiểm thử.');
            if(!in_array($row['status'],['active','locked'],true)) throw new DomainException('Trạng thái tài khoản được bảo vệ.');
            if(($action==='lock' && $row['status']!=='active') || ($action==='unlock' && $row['status']!=='locked')) throw new DomainException('Trạng thái không phù hợp.');
            $sql=match($action){
                'update'=>'email=:value,email_verified_at=NULL',
                'reset'=>'password_hash=:value',
                'lock'=>"status='locked',locked_until=NULL",
                'unlock'=>"status='active',failed_login_count=0,locked_until=NULL",
                default=>throw new DomainException('Thao tác không hợp lệ.')};
            $args=['id'=>$id]; if(in_array($action,['update','reset'],true)) $args['value']=$value;
            $s=$this->db->prepare('UPDATE users SET '.$sql.',updated_at=NOW(6) WHERE id=:id'); $s->execute($args);
        });
    }
}
