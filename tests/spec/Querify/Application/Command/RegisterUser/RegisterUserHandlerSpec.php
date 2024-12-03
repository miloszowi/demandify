<?php

declare(strict_types=1);

namespace spec\Querify\Application\Command\RegisterUser;

use PhpSpec\ObjectBehavior;
use Prophecy\Argument;
use Querify\Application\Command\RegisterUser\RegisterUser;
use Querify\Application\Command\RegisterUser\RegisterUserHandler;
use Querify\Application\Event\UserRegistered\UserRegistered;
use Querify\Domain\User\Email;
use Querify\Domain\User\Exception\UserAlreadyRegisteredException;
use Querify\Domain\User\User;
use Querify\Domain\User\UserRepository;
use Symfony\Component\Messenger\Envelope;
use Symfony\Component\Messenger\MessageBusInterface;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasher;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

class RegisterUserHandlerSpec extends ObjectBehavior
{
    public function let(
        UserRepository $userRepository,
        UserPasswordHasherInterface $userPasswordHasher,
        MessageBusInterface $messageBus
    ): void {
        $this->beConstructedWith(
            $userRepository,
            $userPasswordHasher,
            $messageBus
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
            new User($email, 'First', 'Last', ['ROLE_USER'])
        );

        $this->shouldThrow(UserAlreadyRegisteredException::class)
            ->during(
                '__invoke',
                [new RegisterUser((string)$email, 'plainPassword', 'First', 'Last', ['ROLE_USER'])]
            );
    }

    public function it_handles_the_registration(
        UserRepository $userRepository,
        UserPasswordHasher $passwordHasher,
        MessageBusInterface $messageBus
    ): void {
        $this->beConstructedWith($userRepository, $passwordHasher, $messageBus);

        $email = Email::fromString('non.existing@local.host');
        $command = new RegisterUser((string)$email, 'First', 'Last', 'plainPassword', ['ROLE_USER']);

        $userRepository->save(Argument::type(User::class))->shouldBeCalledOnce();

        $messageBus->dispatch(Argument::type(UserRegistered::class))->willReturn(new Envelope(Argument::any()));
        $passwordHasher->hashPassword(Argument::type(User::class), $command->plainPassword)->willReturn('hashedPassword');
        $userRepository->findByEmail($email)->willReturn(null);

        $this->__invoke($command);
    }
}
