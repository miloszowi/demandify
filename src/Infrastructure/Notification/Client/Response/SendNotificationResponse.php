<?php

declare(strict_types=1);

namespace Demandify\Infrastructure\Notification\Client\Response;

readonly class SendNotificationResponse
{
    public function __construct(
        public string $channel,
        public string $notificationIdentifier,
        public string $content,
        /** @var mixed[] $attachments */
        public array $attachments,
    ) {}
}
