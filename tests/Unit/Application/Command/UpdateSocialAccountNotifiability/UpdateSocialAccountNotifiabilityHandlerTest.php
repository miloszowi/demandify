<?php

declare(strict_types=1);

namespace Demandify\Tests\Unit\Application\Command\UpdateSocialAccountNotifiability;

use Demandify\Application\Command\UpdateSocialAccountNotifiability\UpdateSocialAccountNotifiability;
use Demandify\Application\Command\UpdateSocialAccountNotifiability\UpdateSocialAccountNotifiabilityHandler;
use Demandify\Domain\User\Email;
use Demandify\Domain\User\User;
use Demandify\Domain\User\UserRepository;
use Demandify\Domain\UserSocialAccount\UserSocialAccount;
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
    private MockObject|UserRepository $userRepository;

    protected function setUp(): void
    {
        $this->userRepository = $this->createMock(UserRepository::class);

        $this->handler = new UpdateSocialAccountNotifiabilityHandler($this->userRepository);
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

        $user = new User(Email::fromString('test@local.host'));
        $userSocialAccountMock = new UserSocialAccount($user, $userSocialAccountType, 'external-id');
        $user->linkSocialAccount($userSocialAccountMock);

        $this->userRepository
            ->expects(self::once())
            ->method('getByUuid')
            ->with($userUuid)
            ->willReturn($user)
        ;

        $this->userRepository
            ->expects(self::once())
            ->method('save')
            ->with($user)
        ;

        $this->handler->__invoke($command);
    }
}
