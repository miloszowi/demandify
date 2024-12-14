<?php

declare(strict_types=1);

namespace Querify\Domain\Notification;

use Querify\Domain\Notification\Exception\NotificationNotFoundException;
use Ramsey\Uuid\UuidInterface;

interface NotificationRepository
{
    /**
     * @throws NotificationNotFoundException
     */
    public function getByDemandUuid(UuidInterface $demandUuid): Notification;

    public function save(Notification $notification): void;
}
