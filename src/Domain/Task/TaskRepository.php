<?php

declare(strict_types=1);

namespace Querify\Domain\Task;

interface TaskRepository
{
    public function save(Task $task): void;
}
