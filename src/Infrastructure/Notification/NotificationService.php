<?php

declare(strict_types=1);

namespace Querify\Infrastructure\Notification;

use Querify\Domain\Demand\Demand;
use Querify\Domain\Notification\Notification;
use Querify\Domain\Notification\NotificationRepository;
use Querify\Domain\Notification\NotificationService as NotificationServiceInterface;
use Querify\Domain\Task\Task;
use Querify\Domain\UserSocialAccount\UserSocialAccount;
use Querify\Infrastructure\Notification\Client\NotificationClient;

class NotificationService implements NotificationServiceInterface
{
    public function __construct(
        private readonly NotificationClientResolver $notificationClientImplementationResolver,
        private readonly NotificationRepository $notificationRepository,
    ) {}

    public function notifyAboutNewDemand(Demand $demand, UserSocialAccount $socialAccount): void
    {
        $notificationClient = $this->notificationClientImplementationResolver->get($socialAccount->type);
        $notification = $notificationClient->send(
            NotificationClient::NEW_DEMAND,
            $demand,
            $socialAccount
        );

        $this->notificationRepository->save(
            new Notification(
                $demand,
                NotificationClient::NEW_DEMAND,
                $notification->notificationIdentifier,
                $notification->channel,
                $socialAccount->type,
            )
        );
    }

    public function notifyDemandDecisionMade(UserSocialAccount $socialAccount, Demand $demand): void
    {
        $notificationClient = $this->notificationClientImplementationResolver->get($socialAccount->type);
        $notification = $this->notificationRepository->getByDemandUuid($demand->uuid);

        $notificationClient->update($notification, $demand, $socialAccount);
    }

    public function notifyAboutNewTask(UserSocialAccount $socialAccount, Task $task): void
    {
        // todo
    }
}
