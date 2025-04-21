<?php

declare(strict_types=1);

namespace Demandify\Domain\Demand;

use Demandify\Domain\Demand\Event\DemandApproved;
use Demandify\Domain\Demand\Event\DemandDeclined;
use Demandify\Domain\Demand\Event\DemandSubmitted;
use Demandify\Domain\Eventable;
use Demandify\Domain\EventReleasable;
use Demandify\Domain\Task\DemandExecutor;
use Demandify\Domain\Task\Event\TaskFailed;
use Demandify\Domain\Task\Event\TaskSucceeded;
use Demandify\Domain\Task\Task;
use Demandify\Domain\User\User;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Ramsey\Uuid\Uuid;
use Ramsey\Uuid\UuidInterface;

#[
    ORM\Entity,
    ORM\Table(name: '`demand`'),
    ORM\Index(name: 'service_idx', columns: ['service']),
]
class Demand implements EventReleasable
{
    use Eventable;

    #[
        ORM\Id,
        ORM\Column(name: 'uuid', type: 'uuid', unique: true, nullable: false)
    ]
    public readonly UuidInterface $uuid;

    #[ORM\Column(length: 15, nullable: false, enumType: Status::class)]
    public Status $status;

    #[
        ORM\ManyToOne(targetEntity: User::class),
        ORM\JoinColumn(name: 'approver_uuid', referencedColumnName: 'uuid', unique: false, nullable: true)
    ]
    public ?User $approver = null;

    #[ORM\OneToOne(targetEntity: Task::class, cascade: ['persist', 'remove'])]
    #[ORM\JoinColumn(name: 'task_uuid', referencedColumnName: 'uuid', unique: false, nullable: true)]
    public ?Task $task = null;

    #[ORM\Column(nullable: false)]
    public readonly \DateTimeImmutable $createdAt;

    #[ORM\Column(nullable: false)]
    public \DateTimeImmutable $updatedAt;

    public function __construct(
        #[
            ORM\ManyToOne(targetEntity: User::class),
            ORM\JoinColumn(name: 'requester_uuid', referencedColumnName: 'uuid', unique: false, nullable: false)
        ]
        public readonly User $requester,
        #[ORM\Column(length: 255, nullable: false)]
        public readonly string $service,
        #[ORM\Column(type: Types::TEXT, nullable: false)]
        public readonly string $content,
        #[ORM\Column(type: Types::TEXT, nullable: false)]
        public readonly string $reason,
    ) {
        $this->uuid = Uuid::uuid4();
        $this->status = Status::NEW;
        $this->createdAt = new \DateTimeImmutable();
        $this->updatedAt = $this->createdAt;

        $this->recordThat(new DemandSubmitted($this->uuid));
    }

    public function approveBy(User $user): void
    {
        $this->status = $this->status->progress(Status::APPROVED);
        $this->approver = $user;

        $this->recordThat(new DemandApproved($this->uuid));
    }

    public function declineBy(User $user): void
    {
        $this->status = $this->status->progress(Status::DECLINED);
        $this->approver = $user;

        $this->recordThat(new DemandDeclined($this->uuid));
    }

    public function start(): void
    {
        $this->status = $this->status->progress(Status::IN_PROGRESS);
    }

    public function execute(DemandExecutor $demandExecutor): void
    {
        $task = $demandExecutor->execute($this);

        $this->status = match ($task->success) {
            true => $this->status->progress(Status::EXECUTED),
            false => $this->status->progress(Status::FAILED),
        };

        $this->task = $task;

        $this->recordThat(
            match ($task->success) {
                true => new TaskSucceeded($this->uuid),
                false => new TaskFailed($this->uuid),
            }
        );
    }
}
