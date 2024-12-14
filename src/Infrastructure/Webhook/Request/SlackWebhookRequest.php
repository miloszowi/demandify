<?php

declare(strict_types=1);

namespace Querify\Infrastructure\Webhook\Request;

use Symfony\Component\Serializer\Attribute\SerializedName;
use Symfony\Component\Serializer\Attribute\SerializedPath;

readonly class SlackWebhookRequest
{
    public function __construct(
        #[SerializedName('callback_id')]
        public string $demandUuid,
        #[SerializedPath('[user][id]')]
        public string $slackUserId,
        #[SerializedPath('[actions][0][value]')]
        public string $decision,
    ) {}
}
