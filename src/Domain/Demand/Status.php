<?php

declare(strict_types=1);

namespace Querify\Domain\Demand;

enum Status: string
{
    case NEW = 'NEW';
    case APPROVED = 'APPROVED';
    case IN_PROGRESS = 'IN_PROGRESS';
    case FAILED = 'FAILED';
    case DECLINED = 'DECLINED';
    case EXECUTED = 'EXECUTED';

    /**
     * @return string[]
     */
    public static function asArray(): array
    {
        return array_column(self::cases(), 'name');
    }

    public function isEqualTo(Status $status): bool
    {
        return $status->value === $this->value;
    }
}
