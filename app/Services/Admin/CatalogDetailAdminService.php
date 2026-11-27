<?php
declare(strict_types=1);
namespace App\Services\Admin;
use App\Repositories\Admin\CatalogDetailAdminRepository;
use InvalidArgumentException;
final class CatalogDetailAdminService {
    public function __construct(private CatalogDetailAdminRepository $repo,private string $dir) {}
    public static function text(mixed $v,int $max): string {if(!is_string($v)||strlen($v)>$max||!preg_match('//u',$v))throw new InvalidArgumentException('Dữ liệu văn bản không hợp lệ.');return trim($v);}
    public static function number(mixed $v,int $min,int $max): int {if(!is_string($v)&&!is_int($v))throw new InvalidArgumentException('Số không hợp lệ.');$n=filter_var($v,FILTER_VALIDATE_INT,['options'=>['min_range'=>$min,'max_range'=>$max]]);if($n===false)throw new InvalidArgumentException('Số ngoài giới hạn.');return $n;}
    public static function uuid(): string {$b=random_bytes(16);$b[6]=chr((ord($b[6])&15)|64);$b[8]=chr((ord($b[8])&63)|128);return vsprintf('%s%s-%s-%s-%s-%s%s%s',str_split(bin2hex($b),4));}
    private static function date(mixed $v): string {$s=self::text($v,16);$d=\DateTimeImmutable::createFromFormat('!Y-m-d\TH:i',$s);if(!$d||$d->format('Y-m-d\TH:i')!==$s)throw new InvalidArgumentException('Ngày khuyến mãi không hợp lệ.');return $d->format('Y-m-d H:i:s');}
    public static function config(array $in): array {
        $price=self::number($in['list_price']??null,1,999999999999);$saleRaw=self::text($in['sale_price']??'',12);$sale=$saleRaw===''?null:self::number($saleRaw,1,$price);
        $start=self::text($in['sale_starts_at']??'',16);$end=self::text($in['sale_ends_at']??'',16);
        if($sale!==null){if($sale>=$price||$start===''||$end==='')throw new InvalidArgumentException('Giá khuyến mãi phải thấp hơn giá niêm yết và có đủ ngày.');$start=self::date($start);$end=self::date($end);if($start>=$end)throw new InvalidArgumentException('Ngày kết thúc phải sau ngày bắt đầu.');}else{if($start!==''||$end!=='')throw new InvalidArgumentException('Nhập giá khuyến mãi hoặc bỏ trống cả hai ngày.');$start=$end=null;}
        return ['storage_gb'=>self::number($in['storage_gb']??null,1,65535),'ram_gb'=>self::number($in['ram_gb']??null,1,65535),'list_price'=>$price,'sale_price'=>$sale,'sale_starts_at'=>$start,'sale_ends_at'=>$end,'is_active'=>self::number($in['is_active']??null,0,1)];
    }
    public static function variant(array $in): array {$sku=strtoupper(self::text($in['sku']??'',80));if(!preg_match('/^[A-Z0-9][A-Z0-9_-]{0,79}$/D',$sku))throw new InvalidArgumentException('SKU chỉ gồm chữ, số, gạch ngang hoặc gạch dưới.');return ['configuration_id'=>self::number($in['configuration_id']??null,1,PHP_INT_MAX),'color_id'=>self::number($in['color_id']??null,1,PHP_INT_MAX),'sku'=>$sku,'is_active'=>self::number($in['is_active']??null,0,1)];}
    public static function imageExtension(string $mime): string {return match($mime){'image/jpeg'=>'jpg','image/png'=>'png','image/webp'=>'webp',default=>throw new InvalidArgumentException('Chỉ nhận JPEG, PNG, WebP.')};}
    public static function validKey(string $key): bool {return (bool)preg_match('/^[a-f0-9]{64}\.(jpg|png|webp)$/D',$key);}
    public function mutate(int $product,array $in,array $files): void {
        $action=self::text($in['action']??'',30);$id=self::number($in['id']??'0',0,PHP_INT_MAX);$saved=null;
        try {$this->repo->transaction($product,function(array $p)use($product,$in,$files,$action,$id,&$saved){
            if($action==='config'||$action==='variant'){
                $v=$action==='config'?self::config($in):self::variant($in);
                if($p['status']==='active'&&$v['is_active']===0)throw new InvalidArgumentException('Ẩn sản phẩm trước khi ngừng cấu hình hoặc biến thể.');
                if($action==='config')$this->repo->saveConfig($product,$id,$v,self::uuid());else $this->repo->saveVariant($product,$id,$v,self::uuid());
                if($p['status']==='active'&&$id>0&&!$this->repo->ready($product))throw new InvalidArgumentException('Sản phẩm đang active phải có cấu hình và biến thể hợp lệ. Hãy ẩn trước khi sửa.');
            }elseif($action==='image-remove'){$this->repo->removeImage($product,$id);}
            elseif($action==='image-add'){
                $f=$files['image']??[];if(!is_array($f)||($f['error']??null)!==UPLOAD_ERR_OK||!is_string($f['tmp_name']??null)||!is_uploaded_file($f['tmp_name']))throw new InvalidArgumentException('Chưa chọn ảnh hoặc upload lỗi.');
                $tmp=$f['tmp_name'];$size=filesize($tmp);if($size===false||$size<1||$size>5*1024*1024)throw new InvalidArgumentException('Ảnh tối đa 5 MB.');
                $mime=(new \finfo(FILEINFO_MIME_TYPE))->file($tmp);$info=@getimagesize($tmp);if(!$info||$info[0]>6000||$info[1]>6000||($info['mime']??null)!==$mime)throw new InvalidArgumentException('Ảnh không hợp lệ hoặc quá lớn.');
                $ext=self::imageExtension((string)$mime);$alt=self::text($in['alt_text']??'',255);$sort=self::number($in['sort_order']??'0',0,100000);
                if(!is_dir($this->dir)&&!mkdir($this->dir,0700,true))throw new \RuntimeException('Không tạo được thư mục ảnh.');$key=bin2hex(random_bytes(32)).'.'.$ext;$dest=$this->dir.'/'.$key;
                if(!move_uploaded_file($tmp,$dest))throw new \RuntimeException('Không lưu được ảnh.');$saved=$dest;$this->repo->addImage($product,$key,$alt,$sort);
            }else throw new InvalidArgumentException('Thao tác không hợp lệ.');
        });}catch(\Throwable $e){if($saved!==null&&is_file($saved))@unlink($saved);throw $e;}
    }
    public function imagePath(int $p,int $i): ?string {$row=$this->repo->image($p,$i);if(!$row||!self::validKey($row['storage_key']))return null;$path=$this->dir.'/'.$row['storage_key'];return is_file($path)&&!is_link($path)?$path:null;}
}
