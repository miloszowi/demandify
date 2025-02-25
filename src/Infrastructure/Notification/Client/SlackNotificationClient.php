<?php

namespace Demandify\Infrastructure\Notification\Client;

use Demandify\Domain\Demand\Demand;
use Demandify\Domain\Notification\Notification;
use Demandify\Domain\Notification\NotificationType;
use Demandify\Domain\UserSocialAccount\UserSocialAccount;
use Demandify\Domain\UserSocialAccount\UserSocialAccountType;
use Demandify\Infrastructure\External\Slack\Http\SlackHttpClient;
use Demandify\Infrastructure\Notification\Client\Response\SendNotificationResponse;
use Demandify\Infrastructure\Notification\Content\SlackNotificationBlocksFactory;

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
