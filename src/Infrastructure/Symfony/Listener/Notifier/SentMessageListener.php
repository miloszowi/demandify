<?php

declare(strict_types=1);

namespace Demandify\Infrastructure\Symfony\Listener\Notifier;

use Demandify\Application\Command\CommandBus;
use Demandify\Application\Command\SaveSentNotification\SaveSentNotification;
use Demandify\Domain\UserSocialAccount\UserSocialAccountType;
use Demandify\Infrastructure\Symfony\Notifier\DemandNotificationSubject;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\Notifier\Bridge\Slack\SlackSentMessage;
use Symfony\Component\Notifier\Event\SentMessageEvent;

class SentMessageListener implements EventSubscriberInterface
{
    public function __construct(private readonly CommandBus $commandBus) {}

    public function onSentNotification(SentMessageEvent $event): void
    {
        $demandNotificationSubject = DemandNotificationSubject::fromString(
            $event->getMessage()->getOriginalMessage()->getSubject()
        );

        $message = $event->getMessage();

        $userSocialAccount = match (true) {
            $message instanceof SlackSentMessage => UserSocialAccountType::SLACK,
            default => throw new \RuntimeException('Unsupported message type'),
        };

        $channelId = match (true) {
            $message instanceof SlackSentMessage => $message->getChannelId(),
            default => throw new \RuntimeException('Unsupported message type'),
        };

        $this->commandBus->dispatch(
            new SaveSentNotification(
                $demandNotificationSubject->demandUuid,
                $demandNotificationSubject->notificationType,
                $message->getMessageId(),
                $channelId,
                $message->getOriginalMessage()->getOptions()->toArray(),
                $userSocialAccount,
            )
        );
    }

    public static function getSubscribedEvents(): array
    {
        return [
            SentMessageEvent::class => ['onSentNotification', 0],
        ];
    }
}
