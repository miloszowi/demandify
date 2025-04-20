<?php

declare(strict_types=1);

namespace Demandify\Infrastructure\Webhook\Request;

use Demandify\Infrastructure\Notification\Options\NotificationOptionsFactory;
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
        Assert::oneOf(
            $this->decision,
            [NotificationOptionsFactory::APPROVE_CALLBACK_KEY, NotificationOptionsFactory::DECLINE_CALLBACK_KEY]
        );
    }

    public function isApproved(): bool
    {
        return NotificationOptionsFactory::APPROVE_CALLBACK_KEY === $this->decision;
    }
}
