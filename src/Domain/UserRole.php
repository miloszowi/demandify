<?php

declare(strict_types=1);

namespace Querify\Domain;

enum UserRole: string
{
    case ROLE_USER = 'ROLE_USER';
    case ROLE_ADMIN = 'ROLE_ADMIN';

    /**
     * @return string[]
     */
    public static function asArray(): array
    {
        return \array_column(self::cases(), 'name');
    }
}