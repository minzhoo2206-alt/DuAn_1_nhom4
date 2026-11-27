<?php
declare(strict_types=1);
namespace App\Repositories\Admin;
use App\Services\Admin\RbacAdminService as Rules;
use PDO;
use DomainException;
use Throwable;

final class RbacAdminRepository
{
    public function __construct(private PDO $db) { if ($db->query('SELECT DATABASE()')->fetchColumn()!=='fourtech_admin_test') throw new DomainException('Local only'); }
    private function query(string $sql,array $args=[]): \PDOStatement { $s=$this->db->prepare($sql);$s->execute($args);return $s; }
    private function atomic(callable $fn): void { $this->db->beginTransaction();try{$fn();$this->db->commit();}catch(Throwable $e){if($this->db->inTransaction())$this->db->rollBack();throw $e;} }
    public function actor(int $id): ?array
    {
        $u=$this->query('SELECT id,email,status,password_hash FROM users WHERE id=? AND deleted_at IS NULL',[$id])->fetch(PDO::FETCH_ASSOC);
        if(!$u || !Rules::testEmail($u['email']))return null;
        $roles=$this->query('SELECT r.id,r.code,r.deleted_at FROM user_has_roles ur JOIN roles r ON r.id=ur.role_id WHERE ur.user_id=? AND ur.deleted_at IS NULL',[$id])->fetchAll(PDO::FETCH_ASSOC);
        $role=count($roles)===1 && $roles[0]['deleted_at']===null ? $roles[0] : null;
        $u['role']=$role['code']??'';
        $u['permissions']=$role ? $this->query('SELECT p.code FROM role_has_permissions rp JOIN permissions p ON p.id=rp.permission_id WHERE rp.role_id=? AND rp.deleted_at IS NULL AND p.deleted_at IS NULL',[$role['id']])->fetchAll(PDO::FETCH_COLUMN) : [];
        $u['fingerprint']=hash('sha256',$u['password_hash']);unset($u['password_hash']);return $u;
    }
    public function authenticate(string $email,string $password): ?array
    {
        if(!Rules::testEmail($email)||strlen($password)>72||str_contains($password,"\0"))return null;
        $u=$this->query('SELECT id,password_hash,status FROM users WHERE email=? AND deleted_at IS NULL',[$email])->fetch(PDO::FETCH_ASSOC);
        if(!$u || !password_verify($password,$u['password_hash']) || $u['status']!=='active')return null;
         $actor=$this->actor((int)$u['id']);
        return Rules::sessionValid($actor,hash('sha256',$u['password_hash'])) ? $actor : null;
    }
    public function accounts(int $page=1): array
    {
        $page=max(1,min(10000,$page));$offset=($page-1)*50;
        return $this->query("SELECT u.id,u.email,u.status, (SELECT GROUP_CONCAT(r.code ORDER BY r.code) FROM user_has_roles ur JOIN roles r ON r.id=ur.role_id WHERE ur.user_id=u.id AND ur.deleted_at IS NULL AND r.deleted_at IS NULL) AS role_codes FROM users u WHERE u.deleted_at IS NULL AND u.email LIKE '%@example.test' ORDER BY u.id LIMIT 50 OFFSET ".$offset)->fetchAll(PDO::FETCH_ASSOC);
    }
    public function matrix(): array
    {return $this->query("SELECT r.code AS role_code,p.code AS permission_code FROM roles r JOIN role_has_permissions rp ON rp.role_id=r.id JOIN permissions p ON p.id=rp.permission_id WHERE r.deleted_at IS NULL AND rp.deleted_at IS NULL AND p.deleted_at IS NULL AND r.code IN ('task17_admin','task16_staff','task17_customer') ORDER BY r.code,p.code")->fetchAll(PDO::FETCH_ASSOC);}
    public function changeRole(int $actorId,int $target,string $role): void
    {
        if($actorId===$target || !in_array($role,[Rules::STAFF,Rules::CUSTOMER],true))throw new DomainException('Không được cấp quyền Admin hoặc sửa chính mình.');
        $this->atomic(function()use($actorId,$target,$role){
            $this->query('SELECT id FROM users WHERE id IN (?,?) ORDER BY id FOR UPDATE',[$actorId,$target])->fetchAll();
            if(!Rules::allowed($this->actor($actorId),Rules::RBAC))throw new DomainException('Không đủ quyền.');
            $u=$this->query('SELECT email,status FROM users WHERE id=? AND deleted_at IS NULL',[$target])->fetch(PDO::FETCH_ASSOC);
            $current=$this->query('SELECT r.code,r.deleted_at FROM user_has_roles ur JOIN roles r ON r.id=ur.role_id WHERE ur.user_id=? AND ur.deleted_at IS NULL FOR UPDATE',[$target])->fetchAll(PDO::FETCH_ASSOC);
            if(!$u || !Rules::testEmail($u['email']) || !in_array($u['status'],['active','locked'],true) || count($current)!==1 || $current[0]['deleted_at']!==null || !in_array($current[0]['code'],[Rules::STAFF,Rules::CUSTOMER],true))throw new DomainException('Tài khoản được bảo vệ hoặc không hợp lệ.');
            $rid=$this->query('SELECT id FROM roles WHERE code=? AND deleted_at IS NULL FOR UPDATE',[$role])->fetchColumn();if(!$rid)throw new DomainException('Thiếu role.');
            if($current[0]['code']===$role)return;
            $this->query('UPDATE user_has_roles SET deleted_at=NOW(6),updated_at=NOW(6) WHERE user_id=? AND deleted_at IS NULL',[$target]);
            $this->query('INSERT INTO user_has_roles(user_id,role_id) VALUES(?,?) ON DUPLICATE KEY UPDATE deleted_at=NULL,updated_at=NOW(6)',[$target,$rid]);
        });
    }
    public function staffWrite(int $actorId,bool $allow): void
    {
        $this->atomic(function()use($actorId,$allow){
            $this->query('SELECT id FROM users WHERE id=? FOR UPDATE',[$actorId]);
            if(!Rules::allowed($this->actor($actorId),Rules::RBAC))throw new DomainException('Không đủ quyền.');
            $rid=$this->query('SELECT id FROM roles WHERE code=? AND deleted_at IS NULL FOR UPDATE',[Rules::STAFF])->fetchColumn();
            $pid=$this->query('SELECT id FROM permissions WHERE code=? AND deleted_at IS NULL',[Rules::WRITE])->fetchColumn();if(!$rid||!$pid)throw new DomainException('Thiếu role hoặc permission.');
            if($allow)$this->query('INSERT INTO role_has_permissions(role_id,permission_id) VALUES(?,?) ON DUPLICATE KEY UPDATE deleted_at=NULL,updated_at=NOW(6)',[$rid,$pid]);
            else $this->query('UPDATE role_has_permissions SET deleted_at=NOW(6),updated_at=NOW(6) WHERE role_id=? AND permission_id=? AND deleted_at IS NULL',[$rid,$pid]);
        });
    }
}
