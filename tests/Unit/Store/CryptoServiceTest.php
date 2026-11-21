<?php
declare(strict_types=1);
namespace Tests\Unit\Store;
use PHPUnit\Framework\TestCase;
use App\Services\Store\CryptoService;
use RuntimeException;

final class CryptoServiceTest extends TestCase
{
    public function testEncryptedDataRoundTripAndTamperRejection():void
    {
        $_ENV['APP_KEY']=base64_encode(random_bytes(32));
        $service=new CryptoService();
        $encrypted=$service->encrypt('Địa chỉ kiểm thử FOURTECH');
        self::assertStringStartsWith('v1:',$encrypted);
        self::assertSame('Địa chỉ kiểm thử FOURTECH',$service->decrypt($encrypted));
        $decoded=base64_decode(substr($encrypted,3),true);
        $decoded[40]=chr(ord($decoded[40])^1);
        $tampered='v1:'.base64_encode($decoded);
        $this->expectException(RuntimeException::class);
        $service->decrypt($tampered);
    }
}
