<?php

namespace Querify\Infrastructure\Notification\Client;

use Querify\Domain\Demand\Demand;
use Querify\Domain\Notification\Notification;
use Querify\Domain\Notification\NotificationType;
use Querify\Domain\UserSocialAccount\UserSocialAccount;
use Querify\Domain\UserSocialAccount\UserSocialAccountType;
use Querify\Infrastructure\External\Slack\Http\SlackHttpClient;
use Querify\Infrastructure\Notification\Client\Response\SendNotificationResponse;
use Querify\Infrastructure\Notification\Content\SlackNotificationBlocksFactory;

class SlackNotificationClient implements NotificationClient
{
    public const string APPROVE_CALLBACK_KEY = 'approve';
    public const string DECLINE_CALLBACK_KEY = 'decline';

    public function __construct(
        private readonly SlackHttpClient $slackHttpClient,
        private readonly SlackNotificationBlocksFactory $slackNotificationBlocksFactory,
    ) {}

    public function send(NotificationType $notificationType, Demand $demand, UserSocialAccount $userSocialAccount): SendNotificationResponse
    {
        $blocks = $this->slackNotificationBlocksFactory->create($notificationType, $demand);

        $response = $this->slackHttpClient->sendChatMessage(
            blocks: $blocks,
            recipientSlackId: $userSocialAccount->externalId
        );

        return new SendNotificationResponse(
            $response->channel,
            $response->timestamp,
            '',
            $blocks
        );
    }

    public function update(Notification $notification, Demand $demand): void
    {
        $blocks = $this->slackNotificationBlocksFactory->createForUpdatedDecision($demand);

        $this->slackHttpClient->updateChatMessage($blocks, $notification->channel, $notification->notificationIdentifier);
    }

    public function getType(): UserSocialAccountType
    {
        return UserSocialAccountType::SLACK;
    }

    public function supports(UserSocialAccountType $userSocialAccountType): bool
    {
        return $userSocialAccountType->isEqualTo($this->getType());
    }
}
