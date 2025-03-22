<?php

declare(strict_types=1);

namespace Demandify\Tests\Unit\Application\Command\LinkSocialAccountToUser;

use Demandify\Application\Command\LinkSocialAccountToUser\LinkSocialAccountToUser;
use Demandify\Application\Command\LinkSocialAccountToUser\LinkSocialAccountToUserHandler;
use Demandify\Domain\User\Email;
use Demandify\Domain\User\Exception\UserNotFoundException;
use Demandify\Domain\User\User;
use Demandify\Domain\User\UserRepository;
use Demandify\Domain\UserSocialAccount\Exception\UserSocialAccountAlreadyLinkedException;
use Demandify\Domain\UserSocialAccount\UserSocialAccount;
use Demandify\Domain\UserSocialAccount\UserSocialAccountType;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Ramsey\Uuid\Uuid;

/**
 * @internal
 */
#[CoversClass(LinkSocialAccountToUserHandler::class)]
final class LinkSocialAccountToUserHandlerTest extends TestCase
{
    private MockObject|UserRepository $userRepositoryMock;
    private LinkSocialAccountToUserHandler $linkSocialAccountToUserHandler;

    protected function setUp(): void
    {
        $this->userRepositoryMock = $this->createMock(UserRepository::class);
        $this->linkSocialAccountToUserHandler = new LinkSocialAccountToUserHandler($this->userRepositoryMock);
    }

    public function testLinkingSocialAccountIsSuccessful(): void
    {
        $userMock = $this->createMock(User::class);
        $command = new LinkSocialAccountToUser(
            'example@local.host',
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

    public function testLinkingSocialAccountWillThrowExceptionIfUserNotExists(): void
    {
        $command = new LinkSocialAccountToUser(
            'example@local.host',
            UserSocialAccountType::SLACK,
            'externalId',
            ['some-data' => 'some-value']
        );

        $this->userRepositoryMock
            ->expects(self::once())
            ->method('getByEmail')
            ->with(Email::fromString($command->userEmail))
            ->willThrowException(new UserNotFoundException(''))
        ;

        $this->expectException(UserNotFoundException::class);
        $this->linkSocialAccountToUserHandler->__invoke($command);
    }

    public function testDoesNotLinkSocialAccountIfExists(): void
    {
        $userMock = $this->createMock(User::class);
        // @phpstan-ignore-next-line
        $userMock->uuid = Uuid::uuid4();
        $command = new LinkSocialAccountToUser(
            'example@local.host',
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

        $this->expectException(UserSocialAccountAlreadyLinkedException::class);

        $this->linkSocialAccountToUserHandler->__invoke($command);
    }
}
