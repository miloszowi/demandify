<?php

declare(strict_types=1);

namespace Querify\Infrastructure\Doctrine\Repository;

use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;
use Querify\Domain\Task\Task;
use Querify\Domain\Task\TaskRepository as TaskRepositoryInterface;

class DoctrineAwareTaskRepository extends ServiceEntityRepository implements TaskRepositoryInterface
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Task::class);
    }

    public function save(Task $task): void
    {
        $this->getEntityManager()->persist($task);
        $this->getEntityManager()->flush();
    }
}
