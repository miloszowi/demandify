<?php

declare(strict_types=1);

namespace Demandify\Infrastructure\Api\Resource;

use ApiPlatform\Metadata\ApiProperty;
use ApiPlatform\Metadata\ApiResource;
use ApiPlatform\Metadata\Get;
use ApiPlatform\Metadata\Post;
use Demandify\Domain\Demand\Demand as DomainDemand;
use Demandify\Domain\User\UserRole;
use Demandify\Infrastructure\Api\Processor\DemandProcessor;
use Demandify\Infrastructure\Api\Provider\DemandStateProvider;
use Ramsey\Uuid\UuidInterface;
use Symfony\Component\Serializer\Attribute\Groups;

#[
    ApiResource(
        shortName: 'demand',
        operations: [
            new Get(
                uriTemplate: '/demand/{uuid}',
                security: "is_granted('".UserRole::ROLE_ADMIN->value." or (object.owner == user')",
            ),
            //            new Post(
            //                security: "is_granted('".UserRole::ROLE_USER->value."')",
            //                processor: DemandProcessor::class
            //            ),
        ],
        normalizationContext: ['groups' => ['demand:read']],
        denormalizationContext: ['groups' => ['demand:write']],
        provider: DemandStateProvider::class,
    )
]
readonly class Demand
{
    public function __construct(
        #[
            ApiProperty(identifier: true),
            Groups(['demand:read'])
        ]
        public ?UuidInterface $uuid = null,
        #[Groups(['demand:read'])]
        public ?UuidInterface $requesterUuid = null,
        #[Groups(['demand:read', 'demand:write'])]
        public ?string $service = null,
        #[Groups(['demand:read', 'demand:write'])]
        public ?string $content = null,
        #[Groups(['demand:read', 'demand:write'])]
        public ?string $reason = null,
        #[Groups(['demand:read'])]
        public ?string $createdAt = null,
        #[Groups(['demand:read'])]
        public ?string $updatedAt = null,
        #[Groups(['demand:read'])]
        public ?UuidInterface $approverUuid = null,
    ) {}

    public static function createFromDomainModel(DomainDemand $demand): self
    {
        return new self(
            $demand->uuid,
            $demand->requester->uuid,
            $demand->service,
            $demand->content,
            $demand->reason,
            $demand->createdAt->format('Y-m-d H:i:s'),
            $demand->updatedAt->format('Y-m-d H:i:s'),
            $demand->approver->uuid
        );
    }
}
