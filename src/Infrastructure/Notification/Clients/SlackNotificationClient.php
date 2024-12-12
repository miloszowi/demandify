<?php

namespace Querify\Infrastructure\Notification\Clients;

use Querify\Domain\UserSocialAccount\UserSocialAccount;
use Querify\Domain\UserSocialAccount\UserSocialAccountType;
use Querify\Infrastructure\External\Slack\Http\SlackHttpClient;
use Querify\Infrastructure\Notification\Clients\Response\SendNotificationResponse;
use Querify\Infrastructure\Notification\NotificationClient;

class SlackNotificationClient implements NotificationClient
{
    public function __construct(private readonly SlackHttpClient $slackHttpClient) {}

    public function send(string $content, array $attachments, UserSocialAccount $userSocialAccount): SendNotificationResponse
    {
        $response = $this->slackHttpClient->sendChatMessage($content, $attachments, $userSocialAccount->externalId);

        return new SendNotificationResponse(
            $response->channel,
            [
                'ts' => $response->timestamp,
            ]
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