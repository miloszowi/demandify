<?php

declare(strict_types=1);

namespace Querify\Domain\ExternalService;

use Doctrine\ORM\Mapping as ORM;
use Ramsey\Uuid\UuidInterface;

#[
    ORM\Entity,
    ORM\Table('`external_service_configuration`')
]
class ExternalServiceConfiguration
{
    public function __construct(
        #[
            ORM\Id,
            ORM\Column(type: 'string', nullable: false),
        ]
        public readonly string $serviceName,
        /**
         * @var UuidInterface[]
         */
        #[ORM\Column(type: 'json')]
        public array $eligibleApprovers,
    ) {}
}
