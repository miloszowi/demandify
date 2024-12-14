<?php

declare(strict_types=1);

namespace Querify\Infrastructure\Notification\ContentGenerator;

readonly class NotificationContentDTO
{
    public function __construct(
        public string $content,
        /** @var mixed[] */
        public array $attachments,
    ) {}
}
