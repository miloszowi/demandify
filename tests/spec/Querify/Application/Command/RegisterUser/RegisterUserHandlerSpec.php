<?php

declare(strict_types=1);

namespace spec\Querify\Application\Command\RegisterUser;

use PhpSpec\ObjectBehavior;
use Prophecy\Argument;
use Querify\Application\Command\RegisterUser\RegisterUser;
use Querify\Application\Command\RegisterUser\RegisterUserHandler;
use Querify\Domain\DomainEventPublisher;
use Querify\Domain\User\Email;
use Querify\Domain\User\Event\UserRegistered;
use Querify\Domain\User\Exception\UserAlreadyRegisteredException;
use Querify\Domain\User\User;
use Querify\Domain\User\UserRepository;
use Querify\Domain\User\UserRole;

class RegisterUserHandlerSpec extends ObjectBehavior
{
    public function let(
        UserRepository $userRepository,
        DomainEventPublisher $domainEventPublisher,
    ): void {
        $this->beConstructedWith(
            $userRepository,
            $domainEventPublisher
        );
    }

    public function it_is_initializable(): void
    {
        $this->shouldHaveType(RegisterUserHandler::class);
    }

    public function it_throws_exception_when_email_is_already_registered(
        UserRepository $userRepository,
    ): void {
        $email = Email::fromString('existing@local.host');

        $userRepository->findByEmail($email)->willReturn(
            new User($email, 'First')
        );

        $this->shouldThrow(UserAlreadyRegisteredException::class)
            ->during(
                '__invoke',
                [new RegisterUser((string) $email, 'plainPassword', [UserRole::ROLE_USER])]
            )
        ;
    }

    public function it_handles_the_registration(
        UserRepository $userRepository,
        DomainEventPublisher $domainEventPublisher,
    ): void {
        $this->beConstructedWith($userRepository, $domainEventPublisher);

        $email = Email::fromString('non.existing@local.host');
        $command = new RegisterUser((string) $email, 'First', [UserRole::ROLE_USER]);

        $userRepository->save(Argument::type(User::class))->shouldBeCalledOnce();
        $domainEventPublisher->publish(Argument::type(UserRegistered::class))->shouldBeCalledOnce();

        $userRepository->findByEmail($email)->willReturn(null);

        $this->__invoke($command);
    }
}
