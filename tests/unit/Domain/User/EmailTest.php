<?php

declare(strict_types=1);

namespace Querify\Tests\Unit\Domain\User;

use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;
use Querify\Domain\User\Email;

/**
 * @internal
 */
#[CoversClass(Email::class)]
final class EmailTest extends TestCase
{
    public function testInitializable(): void
    {
        $email = Email::fromString('example@local.host');
        self::assertInstanceOf(Email::class, $email);
    }

    public function testThrowsExceptionIfEmailIsEmpty(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        Email::fromString('');
    }

    public function testThrowsExceptionIfEmailIsInvalid(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        Email::fromString('invalid@email');
    }

    public function testShouldReturnString(): void
    {
        $email = Email::fromString('example@local.host');
        self::assertSame('example@local.host', (string) $email);
    }

    public function testShouldReturnTrueIfOtherEmailIsTheSame(): void
    {
        $email1 = Email::fromString('example@local.host');
        $email2 = Email::fromString('example@local.host');
        self::assertTrue($email1->isEqualTo($email2));
    }

    public function testShouldReturnFalseIfOtherEmailIsNotTheSame(): void
    {
        $email1 = Email::fromString('example@local.host');
        $email2 = Email::fromString('different.email@local.host');
        self::assertFalse($email1->isEqualTo($email2));
    }
}
