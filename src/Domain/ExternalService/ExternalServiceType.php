<?php

declare(strict_types=1);

namespace Querify\Domain\ExternalService;

enum ExternalServiceType: string
{
    case MARIADB = 'mariadb';
    case POSTGRES = 'postgres';
}
