<?php

declare(strict_types=1);

namespace Demandify\Infrastructure\Symfony\Notifier;

use Demandify\Domain\Notification\NotificationType;
use Ramsey\Uuid\Uuid;
use Ramsey\Uuid\UuidInterface;

readonly class DemandNotificationSubject
{
    public function __construct(
        public UuidInterface $demandUuid,
        public NotificationType $notificationType,
    ) {}

    public function __toString(): string
    {
        return \sprintf(
            '%s/%s',
            $this->demandUuid->toString(),
            $this->notificationType->value,
        );
    }

    public static function fromString(string $subject): self
    {
        [$uuid, $notificationType] = explode('/', $subject);

        return new self(
            Uuid::fromString($uuid),
            NotificationType::from($notificationType)
        );
    }
}
