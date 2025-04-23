<?php

declare(strict_types=1);

namespace Demandify\Tests\Unit\Domain\User\Exception;

use Demandify\Domain\User\Email;
use Demandify\Domain\User\Exception\UserNotFoundException;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;
use Ramsey\Uuid\Uuid;

/**
 * @internal
 */
#[CoversClass(UserNotFoundException::class)]
final class UserNotFoundExceptionTest extends TestCase
{
    public function testItCanBeCreatedFromUuid(): void
    {
        $uuid = Uuid::uuid4();
        $exception = UserNotFoundException::fromUuid($uuid);

        self::assertInstanceOf(UserNotFoundException::class, $exception);
        self::assertSame('User with uuid of "'.$uuid->toString().'" was not found.', $exception->getMessage());
    }

    public function testItCanBeCreatedFromEmail(): void
    {
        $email = Email::fromString('test@example.com');
        $exception = UserNotFoundException::fromEmail($email);

        self::assertInstanceOf(UserNotFoundException::class, $exception);
        self::assertSame('User with email of "test@example.com" was not found.', $exception->getMessage());
    }
}
