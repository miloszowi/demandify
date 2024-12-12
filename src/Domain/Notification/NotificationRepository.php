<?php

declare(strict_types=1);

namespace Querify\Domain\Notification;

interface NotificationRepository
{
    public function save(Notification $notification): void;
}
