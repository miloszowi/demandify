<?php

declare(strict_types=1);

namespace Demandify\Infrastructure\Notification;

use Demandify\Domain\Demand\Demand;
use Demandify\Domain\Notification\Notification;
use Demandify\Domain\Notification\NotificationService as NotificationServiceInterface;
use Demandify\Domain\Notification\NotificationType;
use Demandify\Domain\UserSocialAccount\UserSocialAccount;
use Demandify\Infrastructure\Symfony\Notifier\DemandNotificationSubject;
use Psr\Log\LoggerInterface;
use Symfony\Component\Notifier\ChatterInterface;
use Symfony\Component\Notifier\Exception\TransportExceptionInterface;
use Symfony\Component\Notifier\Message\ChatMessage;

class NotificationService implements NotificationServiceInterface
{
    public function __construct(
        private readonly NotificationOptionsFactory $notificationOptionsFactory,
        private readonly ChatterInterface $chatter,
        private readonly LoggerInterface $logger,
    ) {}

    public function send(NotificationType $notificationType, Demand $demand, UserSocialAccount $userSocialAccount): void
    {
        $chatMessage = new ChatMessage(
            (string) (new DemandNotificationSubject($demand->uuid, $notificationType))
        );

        $chatMessageOptions = $this->notificationOptionsFactory->create($demand, $notificationType, $userSocialAccount);
        $chatMessage->options($chatMessageOptions);

        /*
         * Persistence of those messages are handled by other listener as we do not receive
         * SentMessageObject directly out of $chatter->send()
         * @see \Demandify\Infrastructure\Symfony\Listener\Notifier\SentMessageListener
         */
        try {
            $this->chatter->send($chatMessage);
        } catch (TransportExceptionInterface $e) {
            $this->logger->error(
                'Failed to send notification',
                [
                    'exception_message' => $e->getMessage(),
                    'notification_type' => $notificationType->value,
                    'demand_id' => $demand->uuid,
                ]
            );
        }
    }

    public function updateWithDecision(Notification $notification, Demand $demand): void
    {
        $chatMessage = new ChatMessage(
            (string) (new DemandNotificationSubject($demand->uuid, NotificationType::DEMAND_DECIDED))
        );

        $chatMessage->options(
            $this->notificationOptionsFactory->createForDecision($notification, $demand->approver, $demand->status)
        );

        try {
            $this->chatter->send($chatMessage);
        } catch (TransportExceptionInterface $e) {
            $this->logger->error(
                'Failed to send updated notification',
                [
                    'exception_message' => $e->getMessage(),
                    'notification_type' => NotificationType::DEMAND_DECIDED->value,
                    'demand_id' => $demand->uuid,
                ]
            );
        }
    }
}
