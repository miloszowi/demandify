<?php

declare(strict_types=1);

namespace Querfiy\Tests\Unit\Application\Command\LinkSocialAccountToUser;

use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Querify\Application\Command\LinkSocialAccountToUser\LinkSocialAccountToUser;
use Querify\Application\Command\LinkSocialAccountToUser\LinkSocialAccountToUserHandler;
use Querify\Domain\User\Email;
use Querify\Domain\User\Exception\UserNotFoundException;
use Querify\Domain\User\User;
use Querify\Domain\User\UserRepository;
use Querify\Domain\UserSocialAccount\UserSocialAccount;
use Querify\Domain\UserSocialAccount\UserSocialAccountType;
use Symfony\Component\Messenger\Envelope;
use Symfony\Component\Messenger\MessageBusInterface;

/**
 * @internal
 */
#[CoversClass(LinkSocialAccountToUserHandler::class)]
final class LinkSocialAccountToUserHandlerTest extends TestCase
{
    private MockObject|UserRepository $userRepositoryMock;
    private MessageBusInterface|MockObject $messageBusMock;
    private LinkSocialAccountToUserHandler $linkSocialAccountToUserHandler;

    protected function setUp(): void
    {
        $this->userRepositoryMock = $this->createMock(UserRepository::class);
        $this->messageBusMock = $this->createMock(MessageBusInterface::class);
        $this->linkSocialAccountToUserHandler = new LinkSocialAccountToUserHandler($this->userRepositoryMock, $this->messageBusMock);
    }

    public function testLinkingSocialAccountIsSuccessful(): void
    {
        $userMock = $this->createMock(User::class);
        $command = new LinkSocialAccountToUser(
            'example@local.host',
            'username',
            UserSocialAccountType::SLACK,
            'externalId',
            ['some-data' => 'some-value']
        );

        $this->userRepositoryMock
            ->expects(self::once())
            ->method('getByEmail')
            ->with(Email::fromString('example@local.host'))
            ->willReturn($userMock)
        ;
        $userMock
            ->expects(self::once())
            ->method('getSocialAccount')
            ->with(UserSocialAccountType::SLACK)
            ->willReturn(null)
        ;
        $userMock
            ->expects(self::once())
            ->method('linkSocialAccount')
            ->with(self::isInstanceOf(UserSocialAccount::class))
        ;
        $this->userRepositoryMock
            ->expects(self::once())
            ->method('save')
            ->with($userMock)
        ;

        $this->linkSocialAccountToUserHandler->__invoke($command);
    }

    public function testLinkingSocialAccountWillRegisterUserIfNotExists(): void
    {
        $userMock = $this->createMock(User::class);
        $command = new LinkSocialAccountToUser(
            'example@local.host',
            'username',
            UserSocialAccountType::SLACK,
            'externalId',
            ['some-data' => 'some-value']
        );

        $this->userRepositoryMock
            ->expects(self::exactly(2))
            ->method('getByEmail')
            ->with(Email::fromString($command->userEmail))
            ->willReturnOnConsecutiveCalls(
                self::throwException(new UserNotFoundException('')),
                $userMock
            )
        ;

        $mockEnvelope = new Envelope(new \stdClass());
        $this->messageBusMock
            ->expects(self::once())
            ->method('dispatch')
            ->willReturn($mockEnvelope)
        ;
        $userMock
            ->expects(self::once())
            ->method('getSocialAccount')
            ->with($command->type)
            ->willReturn(null)
        ;
        $userMock
            ->expects(self::once())
            ->method('linkSocialAccount')
            ->with(self::isInstanceOf(UserSocialAccount::class))
        ;
        $this->userRepositoryMock
            ->expects(self::once())
            ->method('save')
            ->with($userMock)
        ;

        $this->linkSocialAccountToUserHandler->__invoke($command);
    }

    public function testDoesNotLinkSocialAccountIfExists(): void
    {
        $userMock = $this->createMock(User::class);
        $command = new LinkSocialAccountToUser(
            'example@local.host',
            'username',
            UserSocialAccountType::SLACK,
            'externalId',
            ['some-data' => 'some-value']
        );
        $existingSocialAccount = new UserSocialAccount(
            $userMock,
            $command->type,
            'externalId',
            []
        );

        $this->userRepositoryMock
            ->expects(self::once())
            ->method('getByEmail')
            ->with(Email::fromString($command->userEmail))
            ->willReturn($userMock)
        ;
        $userMock
            ->expects(self::once())
            ->method('getSocialAccount')
            ->with($command->type)
            ->willReturn($existingSocialAccount)
        ;
        $userMock
            ->expects(self::never())
            ->method('linkSocialAccount')
            ->with(self::isInstanceOf(UserSocialAccount::class))
        ;
        $this->userRepositoryMock
            ->expects(self::never())
            ->method('save')
            ->with($userMock)
        ;

        $this->linkSocialAccountToUserHandler->__invoke($command);
    }
}
