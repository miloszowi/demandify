<?php

declare(strict_types=1);

namespace Querify\Infrastructure\Repository;

use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;
use Querify\Domain\Demand\Demand;
use Querify\Domain\Demand\DemandRepository as DemandRepositoryInterface;
use Ramsey\Uuid\UuidInterface;

class DemandRepository extends ServiceEntityRepository implements DemandRepositoryInterface
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Demand::class);
    }

    public function findByUuid(UuidInterface $uuid): Demand
    {
        return $this->getEntityManager()->createQueryBuilder('u')
            ->andWhere('u.uuid = :uuid')
            ->setParameter('uuid', $uuid)
            ->getQuery()
            ->getOneOrNullResult()
        ;
    }

    public function save(Demand $demand): void
    {
        $this->getEntityManager()->persist($demand);
        $this->getEntityManager()->flush($demand);
    }
}
