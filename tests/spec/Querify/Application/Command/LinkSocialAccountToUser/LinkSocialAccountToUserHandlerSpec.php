<?php

declare(strict_types=1);

namespace spec\Querify\Application\Command\LinkSocialAccountToUser;

use PhpSpec\ObjectBehavior;
use Prophecy\Argument;
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

class LinkSocialAccountToUserHandlerSpec extends ObjectBehavior
{
    public function let(
        UserRepository $userRepository,
        MessageBusInterface $messageBus
    ): void {
        $this->beConstructedWith($userRepository, $messageBus);
    }

    public function it_is_initializable(): void
    {
        $this->shouldHaveType(LinkSocialAccountToUserHandler::class);
    }

    public function it_links_social_account_when_user_exists(
        UserRepository $userRepository,
        User $user,
    ): void {
        $command = new LinkSocialAccountToUser(
            'example@local.host',
            'username',
            UserSocialAccountType::SLACK,
            'externalId',
            ['some-data' => 'some-value']
        );

        $userRepository->getByEmail(Email::fromString('example@local.host'))->willReturn($user);
        $user->getSocialAccount(UserSocialAccountType::SLACK)->willReturn(null);

        $user->linkSocialAccount(Argument::type(UserSocialAccount::class))->shouldBeCalled();
        $userRepository->save($user)->shouldBeCalled();

        $this->__invoke($command);
    }

    public function it_registers_user_if_not_found(
        UserRepository $userRepository,
        MessageBusInterface $messageBus,
        User $user
    ): void {
        $command = new LinkSocialAccountToUser(
            'example@local.host',
            'username',
            UserSocialAccountType::SLACK,
            'externalId',
            ['some-data' => 'some-value']
        );

        $callCount = 0;
        $getByEmailAction = static function () use (&$callCount, $user) {
            if (1 === $callCount) {
                return $user;
            }
            ++$callCount;

            throw new UserNotFoundException('');
        };
        $userRepository->getByEmail($command->userEmail)->will($getByEmailAction);
        $mockEnvelope = new Envelope(new \stdClass());
        $messageBus->dispatch(Argument::any())
            ->shouldBeCalledOnce()
            ->willReturn($mockEnvelope)
        ;
        $user->getSocialAccount($command->type)->willReturn(null);
        $user->linkSocialAccount(Argument::type(UserSocialAccount::class))->shouldBeCalled();
        $userRepository->save($user)->shouldBeCalled();

        $this->__invoke($command);
    }

    public function it_does_not_link_social_account_if_exists(
        UserRepository $userRepository,
        User $user,
    ): void {
        $command = new LinkSocialAccountToUser(
            'example@local.host',
            'username',
            UserSocialAccountType::SLACK,
            'externalId',
            ['some-data' => 'some-value']
        );

        $existingSocialAccount = new UserSocialAccount(
            $user->getWrappedObject(),
            $command->type,
            'externalId',
            []
        );

        $userRepository->getByEmail(Email::fromString($command->userEmail))->willReturn($user);
        $user->getSocialAccount($command->type)->willReturn($existingSocialAccount);
        $user->linkSocialAccount(Argument::type(UserSocialAccount::class))->shouldNotBeCalled();
        $userRepository->save($user)->shouldNotBeCalled();

        $this->__invoke($command);
    }
}
