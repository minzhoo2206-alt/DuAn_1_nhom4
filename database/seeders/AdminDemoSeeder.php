<?php
declare(strict_types=1);
namespace App\Database\Seeders;

use PDO;
use RuntimeException;
use Throwable;
use App\Services\Admin\OrderAdminService;

/** Local fixture generator only. Existing rows are never updated or resurrected. */
final class AdminDemoSeeder
{
    private array $stats = [];
    public function __construct(private PDO $db) {}
    public static function data(): array
    {
        return json_decode(file_get_contents(__DIR__.'/admin-demo-data.json'), true, 512, JSON_THROW_ON_ERROR);
    }
    public static function uuid(): string
    {
        $b=random_bytes(16); $b[6]=chr((ord($b[6])&15)|64); $b[8]=chr((ord($b[8])&63)|128);
        return vsprintf('%s%s-%s-%s-%s-%s%s%s',str_split(bin2hex($b),4));
    }
    public static function validate(array $d): void
    {
        foreach (['brands'=>10,'categories'=>20,'products'=>100,'customers'=>3,'orders'=>8] as $key=>$n) {
            if(count($d[$key])!==$n) throw new RuntimeException('Invalid fixture count: '.$key);
        }
        foreach (['products'=>'slug','orders'=>'key'] as $key=>$field) {
            $values=array_column($d[$key],$field);
            if(count(array_unique($values))!==count($values)) throw new RuntimeException('Duplicate fixture identifier: '.$key);
        }
        foreach($d['products'] as $p) {
            if(!isset($d['brands'][$p['brand']],$d['categories'][$p['category']]) || !preg_match('/^task19-phone-[0-9]{3}$/D',$p['slug']) || !in_array($p['status'],['draft','active','hidden'],true)) throw new RuntimeException('Invalid product fixture');
        }
        if(count(array_unique($d['customers']))!==3) throw new RuntimeException('Duplicate customer');
        foreach($d['customers'] as $email) if(!preg_match('/^task19-customer-[0-9]{2}@example\.test$/D',$email)) throw new RuntimeException('Invalid customer');
        foreach($d['orders'] as $o) {
            if(!preg_match('/^task19-demo-[0-9]{3}$/D',$o['key']) || !isset($d['customers'][$o['customer']],$d['products'][$o['product']]) || $o['price']<=0 || $o['quantity']<1 || $o['path'][0]!=='pending_confirmation') throw new RuntimeException('Invalid order fixture');
            for($i=1;$i<count($o['path']);$i++) OrderAdminService::validate($o['path'][$i-1],$o['path'][$i], 'Task 19 demo');
        }
    }
    private function q(string $sql,array $args=[]): \PDOStatement
    {
        $s=$this->db->prepare($sql); $s->execute($args); return $s;
    }
    private function count(string $key,string $kind): void { $this->stats[$key][$kind]++; }
    private function catalog(string $table,array $rows): array
    {
        if(!in_array($table,['brands','categories'],true)) throw new RuntimeException('Invalid table');
        $ids=[];
        foreach($rows as $slug=>$name){
            $r=$this->q("SELECT id,is_active,deleted_at FROM `$table` WHERE slug=?",[$slug])->fetch();
            if($r){
                // Fail atomically rather than link new products to disabled/deleted data.
                if($r['deleted_at']!==null || !(int)$r['is_active']) throw new RuntimeException("Inactive required $table slug: $slug");
                $ids[$slug]=(int)$r['id']; $this->count($table,'existing'); continue;
            }
            $this->q("INSERT INTO `$table`(name,slug,is_active) VALUES(?,?,1)",[$name,$slug]);
            $ids[$slug]=(int)$this->db->lastInsertId(); $this->count($table,'inserted');
        }
        return $ids;
    }
    public function run(array $d): array
    {
        self::validate($d);
        if($this->db->query('SELECT DATABASE()')->fetchColumn()!=='fourtech_admin_test') throw new RuntimeException('Blocked database');
        foreach(['brands','categories','products','users','roles','user_has_roles','permissions','role_has_permissions','orders','order_items','order_status_history'] as $table) $this->db->query("SELECT 1 FROM `$table` LIMIT 0");
        foreach(['brands','categories','products','users','orders'] as $t) $this->stats[$t]=['inserted'=>0,'existing'=>0,'skipped'=>0];
        $this->db->beginTransaction();
        try {
            $brands=$this->catalog('brands',$d['brands']); $categories=$this->catalog('categories',$d['categories']);
            foreach($d['products'] as $p){
                $r=$this->q('SELECT id,deleted_at FROM products WHERE slug=?',[$p['slug']])->fetch();
                if($r){$this->count('products',$r['deleted_at']===null?'existing':'skipped');continue;}
                $this->q('INSERT INTO products(public_id,brand_id,category_id,name,slug,description,warranty_months,status) VALUES(?,?,?,?,?,?,?,?)',[self::uuid(),$brands[$p['brand']],$categories[$p['category']],$p['name'],$p['slug'],'Dữ liệu mẫu Task 19; không phải thông tin bán hàng thực tế.',12,$p['status']]);
                $this->count('products','inserted');
            }
            $role=$this->q("SELECT id FROM roles WHERE code='task17_customer' AND deleted_at IS NULL")->fetchColumn();
            if(!$role) throw new RuntimeException('Missing task17_customer role: import Task 17 first');
            $customers=[];
            foreach($d['customers'] as $i=>$email){
                $r=$this->q('SELECT id,status,deleted_at FROM users WHERE email=?',[$email])->fetch();
                if($r){
                    $roles=$this->q('SELECT r.code FROM user_has_roles ur JOIN roles r ON r.id=ur.role_id WHERE ur.user_id=? AND ur.deleted_at IS NULL AND r.deleted_at IS NULL',[$r['id']])->fetchAll(PDO::FETCH_COLUMN);
                    if($r['deleted_at']!==null || $r['status']!=='active' || $roles!==['task17_customer']){$this->count('users','skipped');continue;}
                    $customers[$i]=(int)$r['id'];$this->count('users','existing');continue;
                }
                // Random unshared password: demo customers are not intended for login.
                $hash=password_hash(bin2hex(random_bytes(32)),PASSWORD_DEFAULT);
                $this->q("INSERT INTO users(public_id,email,password_hash,status) VALUES(?,?,?,'active')",[self::uuid(),$email,$hash]);
                $customers[$i]=(int)$this->db->lastInsertId();
                $this->q('INSERT INTO user_has_roles(user_id,role_id) VALUES(?,?)',[$customers[$i],$role]);
                $this->count('users','inserted');
            }
            foreach($d['orders'] as $o){
                $r=$this->q('SELECT id,deleted_at FROM orders WHERE checkout_key=?',[$o['key']])->fetch();
                if($r){$this->count('orders',$r['deleted_at']===null?'existing':'skipped');continue;}
                if(!isset($customers[$o['customer']])){$this->count('orders','skipped');continue;}
                $path=$o['path'];$status=end($path);$total=$o['price']*$o['quantity'];$fee=30000;
                $now=$this->db->query('SELECT NOW(6)')->fetchColumn();
                $this->q('INSERT INTO orders(public_id,user_id,checkout_key,request_hash,status,items_total,tax_total,shipping_fee,cod_total,confirmed_at,delivered_at,cancelled_at,cancellation_reason) VALUES(?,?,?,UNHEX(SHA2(?,256)),?,?,0,?,?,?,?,?,?)',[
                    self::uuid(),$customers[$o['customer']],$o['key'],$o['key'],$status,$total,$fee,$total+$fee,in_array('confirmed',$path,true)?$now:null,$status==='delivered'?$now:null,$status==='cancelled'?$now:null,$status==='cancelled'?'Hủy đơn mẫu Task 19':null]);
                $id=(int)$this->db->lastInsertId();$p=$d['products'][$o['product']];
                $this->q('INSERT INTO order_items(order_id,product_name,sku,quantity,unit_price,line_tax,line_total) VALUES(?,?,?,?,?,0,?)',[$id,$p['name'],'TASK19-'.strtoupper($p['slug']),$o['quantity'],$o['price'],$total]);
                for($i=1;$i<count($path);$i++) $this->q('INSERT INTO order_status_history(order_id,from_status,to_status,reason) VALUES(?,?,?,?)',[$id,$path[$i-1],$path[$i],'Lịch sử giả lập Task 19']);
                $this->count('orders','inserted');
            }
            $this->db->commit();return $this->stats;
        } catch(Throwable $e){if($this->db->inTransaction())$this->db->rollBack();throw $e;}
    }
}
