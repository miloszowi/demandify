<?php

declare(strict_types=1);

namespace Querify\Infrastructure\Notification\Clients\Response;

use Querify\Domain\UserSocialAccount\UserSocialAccountType;

readonly class SendNotificationResponse
{
    public function __construct(
        public string $channel,
        public array $extraData = []
    ) {}
}
