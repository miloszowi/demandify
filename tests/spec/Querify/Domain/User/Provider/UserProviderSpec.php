<?php

declare(strict_types=1);

namespace spec\Querify\Domain\User\Provider;

use PhpSpec\ObjectBehavior;
use Querify\Domain\User\Email;
use Querify\Domain\User\User;
use Querify\Domain\User\UserRepository;
use Symfony\Component\Security\Core\User\UserProviderInterface;

class UserProviderSpec extends ObjectBehavior
{
    public function let(UserRepository $userRepository): void
    {
        $this->beConstructedWith($userRepository);
    }

    public function it_is_initializable(): void
    {
        $this->shouldHaveType(UserProviderInterface::class);
    }

    public function it_refreshes_user(UserRepository $userRepository): void
    {
        $email = Email::fromString('example@local.host');
        $user = new User($email, 'John Doe');
        $userRepository->getByEmail($email)->shouldBeCalled()->willReturn($user);
        $this->beConstructedWith($userRepository);

        $this->refreshUser($user)->shouldReturn($user);
    }

    public function it_supports_user_class(): void
    {
        $this->supportsClass(User::class)->shouldReturn(true);
    }

    public function it_does_not_support_other_classes(): void
    {
        $this->supportsClass('SomeOtherClass')->shouldReturn(false);
    }

    public function it_loads_user_by_identifier(UserRepository $userRepository): void
    {
        $email = Email::fromString('example@local.host');
        $user = new User($email, 'John Doe');
        $userRepository->getByEmail($email)->shouldBeCalled()->willReturn($user);
        $this->beConstructedWith($userRepository);

        $this->loadUserByIdentifier($user->getUserIdentifier())->shouldReturn($user);
    }
}
