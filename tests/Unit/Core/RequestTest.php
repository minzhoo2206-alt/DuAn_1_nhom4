<?php

declare(strict_types=1);

namespace Tests\Unit\Core;

use App\Core\Request;
use PHPUnit\Framework\TestCase;

final class RequestTest extends TestCase
{
    protected function setUp(): void
    {
        $_GET = [];
        $_POST = [];
    }

    public function testRequestMethod(): void
    {
        $_SERVER['REQUEST_METHOD'] = 'GET';

        $request = new Request();

        self::assertSame('GET', $request->method());
    }

    public function testRequestPath(): void
    {
        $_SERVER['REQUEST_URI'] = '/products?page=2';

        $request = new Request();

        self::assertSame('/products', $request->path());
    }

    public function testRequestQuery(): void
    {
        $_GET['page'] = '2';

        $request = new Request();

        self::assertSame('2', $request->query('page'));
    }

    public function testRequestInput(): void
    {
        $_SERVER['REQUEST_METHOD'] = 'POST';

        $_POST['email'] = 'test@example.com';

        $request = new Request();

        self::assertSame(
            'test@example.com',
            $request->input('email')
        );
    }
}
