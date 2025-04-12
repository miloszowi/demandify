<?php

declare(strict_types=1);

namespace Demandify\Tests\Unit\Domain\User\Provider;

use Demandify\Domain\User\Email;
use Demandify\Domain\User\Provider\UserProvider;
use Demandify\Domain\User\User;
use Demandify\Domain\User\UserRepository;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

/**
 * @internal
 */
#[CoversClass(UserProvider::class)]
final class UserProviderTest extends TestCase
{
    private MockObject|UserRepository $userRepositoryMock;
    private UserProvider $userProvider;

    protected function setUp(): void
    {
        $this->userRepositoryMock = $this->createMock(UserRepository::class);
        $this->userProvider = new UserProvider($this->userRepositoryMock);
    }

    public function testRefreshesUser(): void
    {
        $email = Email::fromString('example@local.host');
        $user = new User($email);
        $this->userRepositoryMock->expects(self::once())->method('getByEmail')->with($email)->willReturn($user);
        $this->userProvider = new UserProvider($this->userRepositoryMock);
        self::assertSame($user, $this->userProvider->refreshUser($user));
    }

    public function testSupportsUserClass(): void
    {
        self::assertTrue($this->userProvider->supportsClass(User::class));
    }

    public function testDoesNotSupportOtherClasses(): void
    {
        self::assertFalse($this->userProvider->supportsClass('SomeOtherClass'));
    }

    public function testLoadsUserByIdentifier(): void
    {
        $email = Email::fromString('example@local.host');
        $user = new User($email);
        $this->userRepositoryMock->expects(self::once())->method('getByEmail')->with($email)->willReturn($user);
        $this->userProvider = new UserProvider($this->userRepositoryMock);
        self::assertSame($user, $this->userProvider->loadUserByIdentifier($user->getUserIdentifier()));
    }
}
