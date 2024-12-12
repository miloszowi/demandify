<?php

declare(strict_types=1);

namespace Querify\Infrastructure\Notification;

use Querify\Domain\Demand\Demand;
use Querify\Domain\Notification\Notification;
use Querify\Domain\Notification\NotificationRepository;
use Querify\Domain\Notification\NotificationService as NotificationInterface;
use Querify\Domain\Task\Task;
use Querify\Domain\UserSocialAccount\UserSocialAccount;
use Querify\Infrastructure\Notification\Clients\NotificationClientImplementationResolver;
use Querify\Infrastructure\Notification\ContentGenerators\NotificationContentGeneratorImplementationResolver;

class NotificationService implements NotificationInterface
{
    public function __construct(
        private readonly NotificationClientImplementationResolver $notificationClientImplementationResolver,
        private readonly NotificationContentGeneratorImplementationResolver $notificationContentGeneratorImplementationResolver,
        private readonly NotificationRepository $notificationRepository,
    ) {}

    public function notifyAboutNewDemand(UserSocialAccount $socialAccount, Demand $demand): void
    {
        $contentGenerator = $this->notificationContentGeneratorImplementationResolver->get($socialAccount->type);
        $notificationClient = $this->notificationClientImplementationResolver->get($socialAccount->type);

        $notification = $notificationClient->send(
            $contentGenerator->generate(
                NotificationContentGenerator::NEW_DEMAND_TEMPLATE,
                [
                    'social_account' => $socialAccount,
                    'demand' => $demand,
                ]
            ),
            $contentGenerator->generateAttachments(
                NotificationContentGenerator::NEW_DEMAND_TEMPLATE,
                $demand->uuid->toString()
            ),
            $socialAccount,
        );

        $this->notificationRepository->save(
            new Notification(
                $demand,
                $notification->channel,
                $notificationClient->getType(),
                $notification->extraData
            )
        );
    }

    public function notifyDemandDecisionMade(UserSocialAccount $socialAccount, Demand $demand): void
    {
        // todo
    }

    public function notifyAboutNewTask(UserSocialAccount $socialAccount, Task $task): void
    {
        // todo
    }
}
