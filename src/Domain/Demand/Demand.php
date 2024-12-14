<?php

declare(strict_types=1);

namespace Querify\Domain\Demand;

use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Querify\Domain\Exception\DomainLogicException;
use Querify\Domain\User\User;
use Ramsey\Uuid\Uuid;
use Ramsey\Uuid\UuidInterface;

#[
    ORM\Entity(repositoryClass: DemandRepository::class),
    ORM\Table(name: '`demand`'),
    ORM\Index(name: 'service_idx', columns: ['service'])
]
class Demand
{
    #[
        ORM\Id,
        ORM\Column(name: 'uuid', type: 'uuid', unique: true, nullable: false)
    ]
    public readonly UuidInterface $uuid;

    #[ORM\Column(length: 15, nullable: false, enumType: Status::class)]
    public Status $status;

    #[
        ORM\Column(type: 'uuid', nullable: false),
        ORM\OneToOne(User::class, mappedBy: 'uuid'),
    ]
    public readonly UuidInterface $requesterUuid;

    #[
        ORM\Column(type: 'uuid', nullable: true),
        ORM\OneToOne(User::class, mappedBy: 'uuid')
    ]
    public ?UuidInterface $approverUuid = null;

    #[ORM\Column(length: 255, nullable: false)]
    public readonly string $service;

    #[ORM\Column(type: Types::TEXT, nullable: false)]
    public readonly string $content;

    #[ORM\Column(type: Types::TEXT, nullable: false)]
    public readonly string $reason;

    #[ORM\Column(nullable: false)]
    public readonly \DateTimeImmutable $createdAt;

    #[ORM\Column(nullable: false)]
    public \DateTimeImmutable $updatedAt;

    public function __construct(
        UuidInterface $requesterUuid,
        string $service,
        string $content,
        string $reason,
    ) {
        $this->uuid = Uuid::uuid4();
        $this->status = Status::NEW;
        $this->requesterUuid = $requesterUuid;
        $this->service = $service;
        $this->content = $content;
        $this->reason = $reason;
        $this->createdAt = new \DateTimeImmutable();
        $this->updatedAt = $this->createdAt;
    }

    public function approveBy(User $user): void
    {
        if (!$this->status->isEqualTo(Status::NEW)) {
            throw new DomainLogicException(\sprintf('Can not approve demand in status other than %s', Status::NEW->value));
        }

        $this->status = Status::APPROVED;
        $this->approverUuid = $user->uuid;

        $this->updatedAt = new \DateTimeImmutable();
    }

    public function declineBy(User $user): void
    {
        if (!$this->status->isEqualTo(Status::NEW)) {
            throw new DomainLogicException(\sprintf('Can not decline demand in status other than %s', Status::NEW->value));
        }

        $this->status = Status::DECLINED;
        $this->approverUuid = $user->uuid;

        $this->updatedAt = new \DateTimeImmutable();
    }
}
