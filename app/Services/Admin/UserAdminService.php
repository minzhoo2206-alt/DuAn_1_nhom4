<?php
declare(strict_types=1);
namespace App\Services\Admin;
use App\Repositories\Admin\UserAdminRepository;
use InvalidArgumentException;
use DomainException;
use OutOfBoundsException;

final class UserAdminService
{
    public const STATUSES=['pending_verification','active','locked','disabled'];
    public function __construct(private UserAdminRepository $repo) {}
    public static function email(mixed $input): string
    {
        $email=is_string($input)?strtolower(trim($input)):'';
        if(strlen($email)>254 || !filter_var($email,FILTER_VALIDATE_EMAIL)) throw new InvalidArgumentException('Email không hợp lệ.');
        return $email;
    }
    public static function password(mixed $input): string
    {
        if(!is_string($input)||strlen($input)<12||strlen($input)>72||str_contains($input,"\0")||trim($input)==='') throw new InvalidArgumentException('Mật khẩu phải có 12–72 byte, không chứa ký tự NULL.');
        return password_hash($input,PASSWORD_DEFAULT);
    }
    public static function uuid(): string
    {
        $b=random_bytes(16); $b[6]=chr((ord($b[6])&15)|64); $b[8]=chr((ord($b[8])&63)|128);
        return vsprintf('%s%s-%s-%s-%s-%s%s%s',str_split(bin2hex($b),4));
    }
    public static function manageable(array $row): bool
    { return ($row['role_codes']??null)==='task16_staff' && in_array($row['status']??null,['active','locked'],true); }
    public function listing(mixed $q,mixed $status,mixed $page): array
    {
        $q=is_string($q)&&strlen($q)<=254?trim($q):'';
        $status=in_array($status,self::STATUSES,true)?$status:'';
        $page=filter_var($page,FILTER_VALIDATE_INT,['options'=>['min_range'=>1]])?:1;
        return $this->repo->listing($q,$status,$page)+compact('q','status');
    }
    public function editable(int $id): array
    {
        $row=$this->repo->find($id); if(!$row) throw new OutOfBoundsException('Không tìm thấy tài khoản.');
        if(!self::manageable($row)) throw new DomainException('Tài khoản được bảo vệ.'); return $row;
    }
    public function create(array $input): int
    { return $this->repo->create(self::email($input['email']??null),self::password($input['password']??null),self::uuid()); }
    public function action(int $id,string $action,array $input): void
    {
        $value=match($action){'update'=>self::email($input['email']??null),'reset'=>self::password($input['password']??null),'lock','unlock'=>null,default=>throw new InvalidArgumentException('Thao tác không hợp lệ.')};
        $this->repo->mutate($id,$action,$value);
    }
}
