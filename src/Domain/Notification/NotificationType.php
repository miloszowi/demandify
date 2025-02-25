<?php

declare(strict_types=1);

namespace Demandify\Domain\Notification;

enum NotificationType: string
{
    case NEW_DEMAND = 'new_demand';
    case DEMAND_APPROVED = 'demand_approved';
    case DEMAND_DECLINED = 'demand_declined';
    case TASK_SUCCEEDED = 'task_succeeded';
    case TASK_FAILED = 'task_failed';
}
