<?php

declare(strict_types=1);

namespace Demandify\Infrastructure\Webhook\Request;

use Demandify\Infrastructure\Notification\Client\SlackNotificationClient;
use Symfony\Component\Serializer\Attribute\SerializedPath;
use Webmozart\Assert\Assert;

readonly class SlackDemandDecisionWebhookRequest
{
    public function __construct(
        #[SerializedPath('[actions][0][block_id]')]
        public string $demandUuid,
        #[SerializedPath('[user][id]')]
        public string $slackUserId,
        #[SerializedPath('[actions][0][value]')]
        public string $decision,
    ) {
        Assert::uuid($this->demandUuid);
        Assert::notEmpty($this->slackUserId);
        Assert::oneOf($this->decision, [SlackNotificationClient::APPROVE_CALLBACK_KEY, SlackNotificationClient::DECLINE_CALLBACK_KEY]);
    }

    public function isApproved(): bool
    {
        return SlackNotificationClient::APPROVE_CALLBACK_KEY === $this->decision;
    }
}
