<?php

declare(strict_types=1);

namespace Demandify\Tests\Unit\Application\Command\UpdateSocialAccountNotifiability;

use Demandify\Application\Command\UpdateSocialAccountNotifiability\UpdateSocialAccountNotifiability;
use Demandify\Application\Command\UpdateSocialAccountNotifiability\UpdateSocialAccountNotifiabilityHandler;
use Demandify\Domain\UserSocialAccount\UserSocialAccount;
use Demandify\Domain\UserSocialAccount\UserSocialAccountRepository;
use Demandify\Domain\UserSocialAccount\UserSocialAccountType;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Ramsey\Uuid\Uuid;

/**
 * @internal
 */
#[CoversClass(UpdateSocialAccountNotifiabilityHandler::class)]
final class UpdateSocialAccountNotifiabilityHandlerTest extends TestCase
{
    private UpdateSocialAccountNotifiabilityHandler $handler;
    private MockObject|UserSocialAccountRepository $userSocialAccountRepositoryMock;

    protected function setUp(): void
    {
        $this->userSocialAccountRepositoryMock = $this->createMock(UserSocialAccountRepository::class);

        $this->handler = new UpdateSocialAccountNotifiabilityHandler($this->userSocialAccountRepositoryMock);
    }

    public function testItIsInitializable(): void
    {
        self::assertInstanceOf(UpdateSocialAccountNotifiabilityHandler::class, $this->handler);
    }

    public function testItUpdatesNotifiability(): void
    {
        $userUuid = Uuid::uuid4();
        $userSocialAccountType = UserSocialAccountType::SLACK;

        $command = new UpdateSocialAccountNotifiability($userUuid, $userSocialAccountType, true);

        $userSocialAccountMock = $this->createMock(UserSocialAccount::class);

        $this->userSocialAccountRepositoryMock
            ->expects(self::once())
            ->method('getByUserUuidAndType')
            ->with($userUuid, $userSocialAccountType)
            ->willReturn($userSocialAccountMock)
        ;

        $userSocialAccountMock
            ->expects(self::once())
            ->method('setNotifiable')
            ->with(true)
        ;

        $this->userSocialAccountRepositoryMock
            ->expects(self::once())
            ->method('save')
            ->with($userSocialAccountMock)
        ;

        $this->handler->__invoke($command);
    }
}
