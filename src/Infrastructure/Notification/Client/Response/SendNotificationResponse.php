<?php

declare(strict_types=1);

namespace Querify\Infrastructure\Notification\Client\Response;

readonly class SendNotificationResponse
{
    public function __construct(
        public string $channel,
        public string $notificationIdentifier,
    ) {}
}
