<?php

declare(strict_types=1);

namespace Demandify\Tests\Unit\Domain\UserSocialAccount;

use Demandify\Domain\User\Email;
use Demandify\Domain\User\User;
use Demandify\Domain\UserSocialAccount\UserSocialAccount;
use Demandify\Domain\UserSocialAccount\UserSocialAccountType;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;

/**
 * @internal
 */
#[CoversClass(UserSocialAccount::class)]
final class UserSocialAccountTest extends TestCase
{
    private User $user;
    private UserSocialAccount $userSocialAccount;

    protected function setUp(): void
    {
        $this->user = new User(Email::fromString('test@local.host'));
        $this->userSocialAccount = new UserSocialAccount(
            $this->user,
            UserSocialAccountType::SLACK,
            '12345',
            ['extraDataKey' => 'extraDataValue']
        );
    }

    public function testItIsInitializable(): void
    {
        self::assertInstanceOf(UserSocialAccount::class, $this->userSocialAccount);
    }

    public function testItHasUser(): void
    {
        self::assertSame($this->user, $this->userSocialAccount->user);
    }

    public function testItHasType(): void
    {
        self::assertSame(UserSocialAccountType::SLACK, $this->userSocialAccount->type);
    }

    public function testItHasExternalId(): void
    {
        self::assertSame('12345', $this->userSocialAccount->externalId);
    }

    public function testItHasExtraData(): void
    {
        self::assertSame(['extraDataKey' => 'extraDataValue'], $this->userSocialAccount->extraData);
    }

    public function testIsNotifiable(): void
    {
        self::assertFalse($this->userSocialAccount->isNotifiable());
    }

    public function testSetNotifiable(): void
    {
        $this->userSocialAccount->setNotifiable(true);
        self::assertTrue($this->userSocialAccount->isNotifiable());
    }
}
