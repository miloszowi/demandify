<?php

declare(strict_types=1);

namespace Demandify\Domain\ExternalService;

use Demandify\Domain\User\User;
use Doctrine\ORM\Mapping as ORM;
use Ramsey\Uuid\Uuid;

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
        public readonly string $externalServiceName,
        /**
         * @var string[]
         */
        #[ORM\Column(type: 'simple_array', nullable: true)]
        public array $eligibleApprovers,
    ) {}

    public function isUserEligible(User $user): bool
    {
        return array_any(
            $this->eligibleApprovers,
            static fn (string $eligibleApprover) => $user->uuid->equals(Uuid::fromString($eligibleApprover))
        );
    }
}
