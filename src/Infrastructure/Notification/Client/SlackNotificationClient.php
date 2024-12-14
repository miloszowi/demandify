<?php

namespace Querify\Infrastructure\Notification\Client;

use Querify\Domain\Demand\Demand;
use Querify\Domain\Demand\Status;
use Querify\Domain\Exception\DomainLogicException;
use Querify\Domain\Notification\Notification;
use Querify\Domain\UserSocialAccount\UserSocialAccount;
use Querify\Domain\UserSocialAccount\UserSocialAccountType;
use Querify\Infrastructure\External\Slack\Http\SlackHttpClient;
use Querify\Infrastructure\Notification\Client\Response\SendNotificationResponse;
use Querify\Infrastructure\Notification\ContentGenerator\SlackNotificationContentGenerator;

class SlackNotificationClient implements NotificationClient
{
    public function __construct(
        private readonly SlackHttpClient $slackHttpClient,
        private readonly SlackNotificationContentGenerator $slackNotificationContentGenerator,
    ) {}

    public function send(string $action, Demand $demand, UserSocialAccount $userSocialAccount): SendNotificationResponse
    {
        $notificationContent = $this->slackNotificationContentGenerator->generate(
            $action,
            $demand,
            $userSocialAccount
        );

        $response = $this->slackHttpClient->sendChatMessage(
            $notificationContent,
            $userSocialAccount->externalId,
        );

        return new SendNotificationResponse(
            $response->channel,
            $response->timestamp
        );
    }

    public function update(Notification $notification, Demand $demand, UserSocialAccount $userSocialAccount): void
    {
        $template = match ($demand->status) {
            Status::APPROVED => NotificationClient::DEMAND_APPROVED,
            Status::DECLINED => NotificationClient::DEMAND_DECLINED,
            default => throw new DomainLogicException('Invalid demand status') // todo: make this better
        };

        $notificationContent = $this->slackNotificationContentGenerator->generate(
            $template,
            $demand,
            $userSocialAccount
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
