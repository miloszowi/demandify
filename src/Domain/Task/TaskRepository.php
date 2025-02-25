<?php

declare(strict_types=1);

namespace Demandify\Domain\Task;

interface TaskRepository
{
    public function save(Task $task): void;
}
