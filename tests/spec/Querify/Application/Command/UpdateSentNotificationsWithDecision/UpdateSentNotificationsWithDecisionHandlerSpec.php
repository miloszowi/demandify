<?php

declare(strict_types=1);

namespace spec\Querify\Application\Command\UpdateSentNotificationsWithDecision;

use PhpSpec\ObjectBehavior;
use Querify\Application\Command\UpdateSentNotificationsWithDecision\UpdateSentNotificationsWithDecision;
use Querify\Domain\Demand\Demand;
use Querify\Domain\Notification\Notification;
use Querify\Domain\Notification\NotificationService;

class UpdateSentNotificationsWithDecisionHandlerSpec extends ObjectBehavior
{
    public function let(NotificationService $notificationService): void
    {
        $this->beConstructedWith($notificationService);
    }

    public function it_updates_sent_notifications_with_decision(
        NotificationService $notificationService,
        Notification $notification,
        Demand $demand,
    ): void {
        $command = new UpdateSentNotificationsWithDecision(
            [$notification->getWrappedObject()],
            $demand->getWrappedObject(),
        );

        $notificationService->update($notification, $command->demand)->shouldBeCalledOnce();

        $this->__invoke($command);
    }
}
