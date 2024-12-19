<?php

declare(strict_types=1);

namespace Querify\Infrastructure\Doctrine\Repository;

use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;
use Querify\Domain\Demand\Demand;
use Querify\Domain\Demand\DemandRepository as DemandRepositoryInterface;
use Querify\Domain\Demand\Exception\DemandNotFoundException;
use Querify\Domain\Demand\Status;
use Querify\Domain\User\User;
use Ramsey\Uuid\UuidInterface;

class DoctrineAwareDemandRepository extends ServiceEntityRepository implements DemandRepositoryInterface
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Demand::class);
    }

    public function getByUuid(UuidInterface $uuid): Demand
    {
        $demand = $this->findByUuid($uuid);

        if (null !== $demand) {
            return $demand;
        }

        throw DemandNotFoundException::fromUuid($uuid);
    }

    public function findByUuid(UuidInterface $uuid): ?Demand
    {
        return $this->createQueryBuilder('d')
            ->leftJoin('d.requester', 'requester')
            ->addSelect('requester')
            ->leftJoin('d.approver', 'approver')
            ->addSelect('approver')
            ->andWhere('d.uuid = :uuid')
            ->setParameter('uuid', $uuid)
            ->getQuery()
            ->getOneOrNullResult()
        ;
    }

    public function findAllFromUser(User $user): array
    {
        return $this->createQueryBuilder('d')
            ->andWhere('d.requester = :requester')
            ->setParameter('requester', $user)
            ->getQuery()
            ->getResult()
        ;
    }

    public function findInStatus(Status $status): array
    {
        return $this->createQueryBuilder('d')
            ->andWhere('d.status = :status')
            ->setParameter('status', $status)
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
