<?php

declare(strict_types=1);

namespace Querify\Domain\Task\Event;

use Querify\Domain\DomainEvent;
use Querify\Domain\Task\Task;

readonly class TaskFailed implements DomainEvent
{
    public function __construct(
        public Task $task,
        private \DateTimeImmutable $occuredAt = new \DateTimeImmutable(),
    ) {}

    public function occuredAt(): \DateTimeImmutable
    {
        return $this->occuredAt;
    }
}
