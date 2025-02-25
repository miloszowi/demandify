<?php

declare(strict_types=1);

namespace Demandify\Tests\Unit\Application\Command\RegisterUser;

use Demandify\Application\Command\RegisterUser\RegisterUser;
use Demandify\Application\Command\RegisterUser\RegisterUserHandler;
use Demandify\Domain\DomainEventPublisher;
use Demandify\Domain\User\Email;
use Demandify\Domain\User\Event\UserRegistered;
use Demandify\Domain\User\Exception\UserAlreadyRegisteredException;
use Demandify\Domain\User\User;
use Demandify\Domain\User\UserRepository;
use Demandify\Domain\User\UserRole;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

/**
 * @internal
 */
#[CoversClass(RegisterUserHandler::class)]
final class RegisterUserHandlerTest extends TestCase
{
    private MockObject|UserRepository $userRepositoryMock;
    private DomainEventPublisher|MockObject $domainEventPublisherMock;
    private RegisterUserHandler $handler;

    protected function setUp(): void
    {
        $this->userRepositoryMock = $this->createMock(UserRepository::class);
        $this->domainEventPublisherMock = $this->createMock(DomainEventPublisher::class);

        $this->handler = new RegisterUserHandler(
            $this->userRepositoryMock,
            $this->domainEventPublisherMock
        );
    }

    public function testHandlesTheRegistration(): void
    {
        $email = Email::fromString('non.existing@local.host');
        $command = new RegisterUser((string) $email, 'First', [UserRole::ROLE_USER]);

        $this->userRepositoryMock
            ->expects(self::once())
            ->method('findByEmail')
            ->with($email)
            ->willReturn(null)
        ;

        $this->userRepositoryMock
            ->expects(self::once())
            ->method('save')
            ->with(self::isInstanceOf(User::class))
        ;

        $this->domainEventPublisherMock
            ->expects(self::once())
            ->method('publish')
            ->with(self::isInstanceOf(UserRegistered::class))
        ;

        $this->handler->__invoke($command);
    }

    public function testThrowsExceptionWhenEmailIsAlreadyRegistered(): void
    {
        $email = Email::fromString('existing@local.host');

        $this->userRepositoryMock
            ->expects(self::once())
            ->method('findByEmail')
            ->with($email)
            ->willReturn(new User($email, 'First'))
        ;

        $this->expectException(UserAlreadyRegisteredException::class);

        $this->handler->__invoke(
            new RegisterUser((string) $email, 'username', [UserRole::ROLE_USER])
        );
    }
}
