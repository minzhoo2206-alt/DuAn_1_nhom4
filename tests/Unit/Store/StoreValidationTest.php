<?php
declare(strict_types=1);
namespace Tests\Unit\Store;
use PHPUnit\Framework\TestCase;
use App\Services\Store\CartService;
use App\Services\Store\CheckoutService;
use App\Services\Store\AuthService;
use App\Services\Store\CryptoService;
use App\Repositories\Store\CartRepository;
use App\Repositories\Store\OrderRepository;
use App\Repositories\Store\UserRepository;
use DomainException;
use ReflectionClass;

final class StoreValidationTest extends TestCase
{
    public function testCartCannotHaveNegativeQuantity():void
    {
        $repo=(new ReflectionClass(CartRepository::class))->newInstanceWithoutConstructor();
        $this->expectException(DomainException::class);
        (new CartService($repo))->set(1,1,-2);
    }
    public function testCheckoutCannotUseInvalidIdempotencyKey():void
    {
        $_ENV['APP_KEY']=base64_encode(random_bytes(32));
        $repo=(new ReflectionClass(OrderRepository::class))->newInstanceWithoutConstructor();
        $this->expectException(DomainException::class);
        (new CheckoutService($repo,new CryptoService()))->place(1,'abc','Test User','0900000000','Test address 123');
    }
    public function testRegisterRejectsInvalidEmail():void
    {
        $_ENV['APP_KEY']=base64_encode(random_bytes(32));
        $repo=(new ReflectionClass(UserRepository::class))->newInstanceWithoutConstructor();
        $this->expectException(DomainException::class);
        (new AuthService($repo,new CryptoService()))->register('bad-email','password-long-enough','Example');
    }
}
