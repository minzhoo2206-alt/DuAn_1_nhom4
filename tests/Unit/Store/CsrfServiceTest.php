<?php
declare(strict_types=1);
namespace Tests\Unit\Store;
use PHPUnit\Framework\TestCase;
use App\Services\Store\CsrfService;

final class CsrfServiceTest extends TestCase
{
    public function testTokenRotation():void
    {
        $_SESSION=[];
        $token=CsrfService::token();
        self::assertTrue(CsrfService::verify($token));
        self::assertFalse(CsrfService::verify('wrong'));
        CsrfService::rotate();
        self::assertFalse(CsrfService::verify($token));
    }
}
