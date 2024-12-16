<?php

declare(strict_types=1);

namespace Querify\Infrastructure\Webhook\Request;

use Querify\Infrastructure\Notification\Client\SlackNotificationClient;
use Symfony\Component\Serializer\Attribute\SerializedName;
use Symfony\Component\Serializer\Attribute\SerializedPath;

readonly class SlackDemandDecisionWebhookRequest
{
    public function __construct(
        #[SerializedName('callback_id')]
        public string $demandUuid,
        #[SerializedPath('[user][id]')]
        public string $slackUserId,
        #[SerializedPath('[actions][0][value]')]
        public string $decision,
    ) {}

    public function isApproved(): bool
    {
        return SlackNotificationClient::APPROVE_CALLBACK_KEY === $this->decision;
    }
}
