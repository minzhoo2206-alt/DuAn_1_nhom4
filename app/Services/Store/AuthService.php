<?php
declare(strict_types=1);
namespace App\Services\Store;

use App\Repositories\Store\UserRepository;
use DomainException;
use PDOException;

final class AuthService
{
    public function __construct(private UserRepository $users, private CryptoService $crypto) {}

    public function register(string $email, string $password, string $name): int
    {
        $email=strtolower(trim($email)); $name=trim($name);
        if (!filter_var($email, FILTER_VALIDATE_EMAIL) || strlen($email)>254)
            throw new DomainException('Email không hợp lệ.');
        if (strlen($password)<10 || strlen($password)>72)
            throw new DomainException('Mật khẩu phải từ 10 đến 72 byte.');
        if ($name==='' || strlen($name)>200) throw new DomainException('Họ tên phải có từ 1 đến 200 byte.');
        $hash=password_hash($password, PASSWORD_DEFAULT);
        $autoActivate=($_ENV['APP_ENV']??'production')==='local'
            && ($_ENV['ALLOW_DEMO_AUTO_VERIFY']??'false')==='true';
        try {
            return $this->users->create($email,$hash,$this->crypto->encrypt($name),$autoActivate);
        } catch (PDOException $e) {
            if ((string)$e->getCode()==='23000') throw new DomainException('Không thể đăng ký bằng email này.');
            throw $e;
        }
    }

    public function login(string $email, string $password): int
    {
        $email=strtolower(trim($email));
        $record=$this->users->findByEmail($email);
        // Chạy password_verify với hash giả khi email không tồn tại để giảm khác biệt timing.
        $dummy='$2y$10$4JiLgPvtHOPq6gxGD.0rDuefmA/7jxRxDxPBxlo5kDXi7F40qvLsm';
        if (!$record) { password_verify($password,$dummy); throw new DomainException('Email hoặc mật khẩu không đúng.'); }
        if ($record['locked_until'] !== null && strtotime($record['locked_until'].' UTC')>time()) {
            throw new DomainException('Tài khoản tạm khóa 15 phút do đăng nhập sai quá nhiều.');
        }
        if (!password_verify($password,(string)$record['password_hash'])) {
            $this->users->loginFailed((int)$record['id']);
            throw new DomainException('Email hoặc mật khẩu không đúng.');
        }
        if ($record['status']!=='active') throw new DomainException('Tài khoản chưa được kích hoạt hoặc đang bị khóa.');
        $this->users->loginSucceeded((int)$record['id']);
        return (int)$record['id'];
    }

    public function profile(int $id): ?array
    {
        $u=$this->users->findById($id);
        if (!$u || $u['status']!=='active') return null;
        $u['full_name']=$this->crypto->decrypt($u['full_name_ciphertext']);
        unset($u['full_name_ciphertext']);
        return $u;
    }
    public function changeName(int $id,string $name): void
    {
        $name=trim($name);
        if ($name==='' || strlen($name)>200) throw new DomainException('Họ tên không hợp lệ.');
        $this->users->updateName($id,$this->crypto->encrypt($name));
    }
}
