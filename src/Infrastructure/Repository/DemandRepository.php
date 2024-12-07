<?php

declare(strict_types=1);

namespace Querify\Infrastructure\Repository;

use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;
use Querify\Domain\Demand\Demand;
use Querify\Domain\Demand\DemandRepository as DemandRepositoryInterface;
use Querify\Domain\Demand\Exception\DemandNotFoundException;
use Querify\Domain\User\User;
use Ramsey\Uuid\UuidInterface;

class DemandRepository extends ServiceEntityRepository implements DemandRepositoryInterface
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Demand::class);
    }

    public function getByUuid(UuidInterface $uuid): Demand
    {
        $demand = $this->createQueryBuilder('d')
            ->andWhere('d.uuid = :uuid')
            ->setParameter('uuid', $uuid->toString())
            ->getQuery()
            ->getOneOrNullResult()
        ;

        if (null !== $demand) {
            return $demand;
        }

        throw DemandNotFoundException::fromUuid($uuid);
    }

    public function findByUuid(UuidInterface $uuid): Demand
    {
        return $this->createQueryBuilder('d')
            ->andWhere('d.uuid = :uuid')
            ->setParameter('uuid', $uuid)
            ->getQuery()
            ->getOneOrNullResult()
        ;
    }

    public function findAllFromUser(User $user): array
    {
        return $this->createQueryBuilder('d')
            ->andWhere('d.requesterUuid = :requester_uuid')
            ->setParameter('requester_uuid', $user->uuid->toString())
            ->getQuery()
            ->getResult()
        ;
    }

    public function save(Demand $demand): void
    {
        $this->getEntityManager()->persist($demand);
        $this->getEntityManager()->flush();
    }
}
