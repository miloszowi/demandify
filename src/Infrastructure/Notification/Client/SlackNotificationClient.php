<?php

namespace Querify\Infrastructure\Notification\Client;

use Querify\Domain\Demand\Demand;
use Querify\Domain\Notification\Notification;
use Querify\Domain\Notification\NotificationType;
use Querify\Domain\UserSocialAccount\UserSocialAccount;
use Querify\Domain\UserSocialAccount\UserSocialAccountType;
use Querify\Infrastructure\External\Slack\Http\SlackHttpClient;
use Querify\Infrastructure\Notification\Client\Response\SendNotificationResponse;
use Querify\Infrastructure\Notification\ContentGenerator\NotificationContentDTO;
use Querify\Infrastructure\Notification\ContentGenerator\SlackNotificationContentGenerator;

class SlackNotificationClient implements NotificationClient
{
    public const APPROVE_CALLBACK_KEY = 'approve';
    public const DECLINE_CALLBACK_KEY = 'decline';

    public function __construct(
        private readonly SlackHttpClient $slackHttpClient,
        private readonly SlackNotificationContentGenerator $slackNotificationContentGenerator,
    ) {}

    public function send(NotificationType $notificationType, Demand $demand, UserSocialAccount $userSocialAccount): SendNotificationResponse
    {
        $notificationContent = $this->slackNotificationContentGenerator->generate(
            $notificationType,
            $demand,
            $userSocialAccount
        );

        $response = $this->slackHttpClient->sendChatMessage(
            $notificationContent,
            $userSocialAccount->externalId,
        );

        return new SendNotificationResponse(
            $response->channel,
            $response->timestamp,
            $notificationContent->content,
            $notificationContent->attachments
        );
    }

    public function update(Notification $notification, Demand $demand): void
    {
        $notificationContent = new NotificationContentDTO(
            $notification->content,
            $this->slackNotificationContentGenerator->generateDecisionUpdateAttachment($demand->approver, $demand->status),
            $notification->channel
        );

        $this->slackHttpClient->updateChatMessage(
            $notificationContent,
            $notification->channel,
            $notification->notificationIdentifier,
        );
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
