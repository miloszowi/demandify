<?php

declare(strict_types=1);

namespace Querify\Domain\Notification;

enum NotificationType: string
{
    case NEW_DEMAND = 'new_demand';
    case DEMAND_APPROVED = 'demand_approved';
    case DEMAND_DECLINED = 'demand_declined';
}
