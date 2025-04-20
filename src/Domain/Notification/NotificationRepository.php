<?php

declare(strict_types=1);

namespace Demandify\Domain\Notification;

use Ramsey\Uuid\UuidInterface;

interface NotificationRepository
{
    /**
     * @return Notification[]
     */
    public function findByDemandAndType(UuidInterface $demandUuid, NotificationType $notificationType): array;

    public function findByNotificationIdentifier(string $notificationIdentifier): ?Notification;

    public function save(Notification $notification): void;
}
