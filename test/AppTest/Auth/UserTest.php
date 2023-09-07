<?php

declare(strict_types=1);

namespace AppTest\Auth;

use App\Auth\User;
use PHPUnit\Framework\TestCase;
use Throwable;

use function json_decode;
use function json_encode;

class UserTest extends TestCase
{
    public function testExceptionThrownWhenSubjectNotKnown(): void
    {
        $this->expectException(Throwable::class);
        User::fromArray([]);
    }

    public function testUserCanBeCreated(): void
    {
        $user = User::fromArray(['sub' => 'some-id']);
        self::assertSame('some-id', $user->getIdentity());
    }

    public function testGetDetail(): void
    {
        $user = User::fromArray(['sub' => 'some-id', 'foo' => 'bar']);
        self::assertSame('bar', $user->getDetail('foo'));
        self::assertNull($user->getDetail('not-there'));
        self::assertSame('baz', $user->getDetail('not-there', 'baz'));
    }

    public function testDetailsMatchInput(): void
    {
        $input = [
            'sub' => 'foo',
            'bar' => 'baz',
        ];

        $user = User::fromArray($input);
        self::assertSame($input, $user->getDetails());
    }

    public function testJsonRoundTrip(): void
    {
        $input = [
            'sub' => 'foo',
            'bar' => 'baz',
        ];

        $user = User::fromArray($input);
        $serialized = json_encode($user);
        $unserialized = json_decode($serialized, true);
        self::assertIsArray($unserialized);

        self::assertSame($input, User::fromArray($unserialized)->getDetails());
    }

    public function testJsonSerializeIsTheDetailsPayload(): void
    {
        $input = [
            'sub' => 'foo',
            'bar' => 'baz',
        ];
        $user = User::fromArray($input);
        self::assertSame($input, $user->jsonSerialize());
    }
}
