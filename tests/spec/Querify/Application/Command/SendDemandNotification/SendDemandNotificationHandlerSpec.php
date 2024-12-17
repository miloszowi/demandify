<?php

declare(strict_types=1);

namespace spec\Querify\Application\Command\SendDemandNotification;

use Doctrine\Common\Collections\ArrayCollection;
use PhpSpec\ObjectBehavior;
use Prophecy\Argument;
use Querify\Application\Command\SendDemandNotification\SendDemandNotification;
use Querify\Domain\Demand\Demand;
use Querify\Domain\Notification\Notification;
use Querify\Domain\Notification\NotificationRepository;
use Querify\Domain\Notification\NotificationService;
use Querify\Domain\Notification\NotificationType;
use Querify\Domain\User\User;
use Querify\Domain\User\UserRepository;
use Querify\Domain\UserSocialAccount\UserSocialAccount;
use Querify\Domain\UserSocialAccount\UserSocialAccountType;
use Ramsey\Uuid\Uuid;

class SendDemandNotificationHandlerSpec extends ObjectBehavior
{
    public function let(
        UserRepository $userRepository,
        NotificationService $notificationService,
        NotificationRepository $notificationRepository
    ): void {
        $this->beConstructedWith($userRepository, $notificationService, $notificationRepository);
    }

    public function it_sends_notifications_and_saves(
        UserRepository $userRepository,
        NotificationService $notificationService,
        NotificationRepository $notificationRepository,
        User $recipient,
        Notification $notification,
        Demand $demand,
    ): void {
        $notificationCommand = new SendDemandNotification(
            Uuid::uuid4(),
            $demand->getWrappedObject(),
            NotificationType::NEW_DEMAND
        );
        $userRepository->getByUuid($notificationCommand->recipientUuid)->willReturn($recipient);

        $recipient->getSocialAccounts()->willReturn(
            new ArrayCollection([
                new UserSocialAccount(
                    $recipient->getWrappedObject(),
                    UserSocialAccountType::SLACK,
                    'externalId',
                ),
            ])
        );

        $notificationService->send(
            $notificationCommand->notificationType,
            $notificationCommand->demand,
            Argument::type(UserSocialAccount::class),
        )->willReturn($notification);

        $notificationRepository->save($notification)->shouldBeCalledOnce();

        $this->__invoke($notificationCommand);
    }

    public function it_handles_no_social_accounts(
        UserRepository $userRepository,
        NotificationService $notificationService,
        NotificationRepository $notificationRepository,
        User $recipient,
        Notification $notification,
        Demand $demand,
    ): void {
        $notificationCommand = new SendDemandNotification(
            Uuid::uuid4(),
            $demand->getWrappedObject(),
            NotificationType::NEW_DEMAND
        );
        $userRepository->getByUuid($notificationCommand->recipientUuid)->willReturn($recipient);

        $recipient->getSocialAccounts()->willReturn(
            new ArrayCollection([])
        );

        $notificationService->send(
            $notificationCommand->notificationType,
            $notificationCommand->demand,
            Argument::type(UserSocialAccount::class),
        )->willReturn($notification);

        $notificationRepository->save($notification)->shouldNotBeCalled();

        $this->__invoke($notificationCommand);
    }
}
