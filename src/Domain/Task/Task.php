<?php

declare(strict_types=1);

namespace Querify\Domain\Task;

use Doctrine\ORM\Mapping as ORM;
use Querify\Domain\Demand\Demand;
use Ramsey\Uuid\Uuid;
use Ramsey\Uuid\UuidInterface;

#[ORM\Entity(repositoryClass: TaskRepository::class)]
readonly class Task
{
    #[
        ORM\Id,
        ORM\Column(type: 'uuid', nullable: false)
    ]
    public UuidInterface $uuid;

    #[ORM\Column(type: 'datetimetz_immutable')]
    public ?\DateTimeImmutable $executedAt;

    public function __construct(
        #[
            ORM\OneToOne(targetEntity: Demand::class),
            ORM\JoinColumn(name: 'demand_uuid', referencedColumnName: 'uuid')
        ]
        public Demand $demand,
        #[ORM\Column(type: 'boolean', nullable: false)]
        public bool $success,
        #[ORM\Column(type: 'integer', nullable: false)]
        public int $executionTime,
        #[ORM\Column(type: 'text', nullable: true)]
        public ?string $errorMessage = null,
        #[ORM\Column(length: 255, nullable: true)]
        public ?string $resultPath = null
    ) {
        $this->uuid = Uuid::uuid4();
        $this->executedAt = new \DateTimeImmutable();
    }
}
