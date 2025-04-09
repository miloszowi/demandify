<?php

declare(strict_types=1);

namespace Demandify\Domain\Task;

use Demandify\Domain\Demand\Demand;
use Doctrine\ORM\Mapping as ORM;
use Ramsey\Uuid\Uuid;
use Ramsey\Uuid\UuidInterface;

#[ORM\Entity]
class Task
{
    #[
        ORM\Id,
        ORM\Column(type: 'uuid', nullable: false)
    ]
    public UuidInterface $uuid;

    #[ORM\Column(type: 'datetimetz_immutable')]
    public readonly ?\DateTimeImmutable $executedAt;

    public function __construct(
        #[
            ORM\OneToOne(targetEntity: Demand::class),
            ORM\JoinColumn(name: 'demand_uuid', referencedColumnName: 'uuid')
        ]
        public readonly Demand $demand,
        #[ORM\Column(type: 'boolean', nullable: false)]
        public readonly bool $success,
        #[ORM\Column(type: 'integer', nullable: false)]
        public readonly int $executionTime,
        #[ORM\Column(type: 'text', nullable: true)]
        public readonly ?string $errorMessage = null,
        #[ORM\Column(length: 255, nullable: true)]
        public readonly ?string $resultPath = null
    ) {
        $this->uuid = Uuid::uuid4();
        $this->executedAt = new \DateTimeImmutable();
    }
}
