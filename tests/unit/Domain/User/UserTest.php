<?php

declare(strict_types=1);

namespace Tests\Demandify\Domain\User;

use Demandify\Domain\User\Email;
use Demandify\Domain\User\User;
use Demandify\Domain\User\UserRole;
use Demandify\Domain\UserSocialAccount\UserSocialAccount;
use Demandify\Domain\UserSocialAccount\UserSocialAccountType;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;
use Ramsey\Uuid\UuidInterface;

/**
 * @internal
 */
#[CoversClass(User::class)]
final class UserTest extends TestCase
{
    private User $user;

    protected function setUp(): void
    {
        $this->user = new User(Email::fromString('example@local.host'), 'John Doe');
    }

    public function testHasAUuid(): void
    {
        self::assertInstanceOf(UuidInterface::class, $this->user->uuid);
    }

    public function testHasCreatedAndUpdatedAtDates(): void
    {
        self::assertInstanceOf(\DateTimeImmutable::class, $this->user->createdAt);
        self::assertInstanceOf(\DateTimeImmutable::class, $this->user->updatedAt);
    }

    public function testHasADefaultRole(): void
    {
        self::assertContains(UserRole::ROLE_USER, $this->user->getRoles());
    }

    public function testCanGrantPrivilege(): void
    {
        $role = UserRole::ROLE_USER;
        $this->user->grantPrivilege($role);
        self::assertContains($role, $this->user->getRoles());
    }

    public function testDoesNotGrantDuplicatePrivilege(): void
    {
        $role = UserRole::ROLE_USER;
        $this->user->grantPrivilege($role);
        $this->user->grantPrivilege($role);
        self::assertContains($role, $this->user->getRoles());
        self::assertCount(1, $this->user->getRoles());
    }

    public function testCanCheckPrivilege(): void
    {
        $role = UserRole::ROLE_USER;
        $this->user->grantPrivilege($role);
        self::assertTrue($this->user->hasPrivilege($role));
    }

    public function testCanLinkSocialAccount(): void
    {
        $socialAccount = new UserSocialAccount(
            $this->user,
            UserSocialAccountType::SLACK,
            'some-external-id',
            []
        );
        $this->user->linkSocialAccount($socialAccount);
        self::assertContains($socialAccount, $this->user->getSocialAccounts());
    }

    public function testCanCheckIfSocialAccountIsLinked(): void
    {
        $socialAccount = new UserSocialAccount(
            $this->user,
            UserSocialAccountType::SLACK,
            'some-external-id',
            []
        );
        $this->user->linkSocialAccount($socialAccount);
        self::assertTrue($this->user->hasSocialAccountLinked(UserSocialAccountType::SLACK));
    }

    public function testReturnsNullIfSocialAccountIsNotLinked(): void
    {
        self::assertFalse($this->user->hasSocialAccountLinked(UserSocialAccountType::SLACK));
    }

    public function testCanGetSocialAccountByType(): void
    {
        $socialAccount = new UserSocialAccount(
            $this->user,
            UserSocialAccountType::SLACK,
            'some-external-id',
            []
        );
        $this->user->linkSocialAccount($socialAccount);
        self::assertSame($socialAccount, $this->user->getSocialAccount(UserSocialAccountType::SLACK));
    }

    public function testReturnNullWhenSocialAccountNotFound(): void
    {
        self::assertNull($this->user->getSocialAccount(UserSocialAccountType::SLACK));
    }

    public function testCanCompareUsers(): void
    {
        self::assertTrue($this->user->isEqualTo($this->user));
        $otherUser = new User(Email::fromString('example@local.host'), 'John Doe');
        self::assertTrue($this->user->isEqualTo($otherUser));
    }

    public function testReturnFalseWhenComparingWithDifferentUser(): void
    {
        $otherUser = new User(
            Email::fromString('another@local.host'),
            'Maria'
        );
        self::assertFalse($this->user->isEqualTo($otherUser));
    }
}
