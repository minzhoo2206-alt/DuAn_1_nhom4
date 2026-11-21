<?php
declare(strict_types=1);
namespace App\Services\Store;

use App\Core\Database;
use App\Repositories\Store\UserRepository;
use DomainException;
use PDO;

/** Xác minh email 1 lần, token chỉ được lưu dưới dạng SHA-256 trong MySQL. */
final class EmailVerificationService
{
    public function __construct(private ?PDO $db = null)
    {
        $this->db ??= Database::connection();
    }

    public function send(string $email): void
    {
        $email = strtolower(trim($email));
        $user = (new UserRepository($this->db))->findByEmail($email);
        // Resend không tiết lộ email có đăng ký hay không.
        if (!$user || $user['status'] !== 'pending_verification') return;
        $sender = (string)($_ENV['MAIL_FROM'] ?? '');
        $base = rtrim((string)($_ENV['APP_URL'] ?? ''), '/');
        if (!filter_var($sender, FILTER_VALIDATE_EMAIL) || !filter_var($base, FILTER_VALIDATE_URL)) {
            throw new DomainException('Email chưa được cấu hình. Hãy liên hệ thành viên phụ trách hosting.');
        }
        $recent = $this->db->prepare('SELECT COUNT(*) FROM email_verification_tokens
            WHERE user_id=? AND created_at > DATE_SUB(NOW(6), INTERVAL 60 SECOND)');
        $recent->execute([(int)$user['id']]);
        if ((int)$recent->fetchColumn()>0) {
            throw new DomainException('Vui lòng chờ ít nhất 60 giây trước khi gửi lại.');
        }
        $token = bin2hex(random_bytes(32));
        $sql = 'INSERT INTO email_verification_tokens (user_id,token_hash,expires_at)
                VALUES (:user_id,:hash,DATE_ADD(UTC_TIMESTAMP(6), INTERVAL 24 HOUR))';
        $st = $this->db->prepare($sql);
        $st->bindValue(':user_id',(int)$user['id'],PDO::PARAM_INT);
        $st->bindValue(':hash',hash('sha256',$token,true),PDO::PARAM_LOB);
        $st->execute();
        $url = $base . '/verify-email?token=' . rawurlencode($token);
        $subject = 'FOURTECH - Xac minh email';
        $body = "Mo lien ket sau de xac minh email (hieu luc 24 gio):\r\n" . $url . "\r\n";
        $headers = 'From: ' . $sender . "\r\nContent-Type: text/plain; charset=UTF-8\r\n";
        if (!mail($email, $subject, $body, $headers)) {
            throw new DomainException('Máy chủ chưa gửi được email. Vui lòng liên hệ nhóm để kiểm tra cấu hình email.');
        }
    }

    public function confirm(string $token): void
    {
        if (!preg_match('/^[a-f0-9]{64}$/D',$token)) throw new DomainException('Liên kết không hợp lệ.');
        $hash = hash('sha256',$token,true);
        $this->db->beginTransaction();
        try {
            $st=$this->db->prepare('SELECT t.id,t.user_id,t.expires_at,u.status
                FROM email_verification_tokens t JOIN users u ON u.id=t.user_id
                WHERE t.token_hash=:hash AND t.used_at IS NULL FOR UPDATE');
            $st->bindValue(':hash',$hash,PDO::PARAM_LOB);$st->execute();
            $row=$st->fetch(PDO::FETCH_ASSOC);
            if (!$row || $row['status']!=='pending_verification' ||
                !$row['expires_at'] || strtotime((string)$row['expires_at'].' UTC') < time()) {
                throw new DomainException('Mã xác minh đã hết hạn hoặc đã được sử dụng.');
            }
            $st=$this->db->prepare("UPDATE users SET status='active',email_verified_at=UTC_TIMESTAMP(6) WHERE id=? AND status='pending_verification'");
            $st->execute([(int)$row['user_id']]);
            if ($st->rowCount()!==1) throw new DomainException('Tài khoản không còn chờ xác minh.');
            $st=$this->db->prepare('UPDATE email_verification_tokens SET used_at=UTC_TIMESTAMP(6) WHERE id=?');
            $st->execute([(int)$row['id']]);
            $this->db->commit();
        } catch (\Throwable $e) {if($this->db->inTransaction())$this->db->rollBack();throw $e;}
    }
}
