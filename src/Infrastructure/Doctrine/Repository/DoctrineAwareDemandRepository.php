<?php

declare(strict_types=1);

namespace Demandify\Infrastructure\Doctrine\Repository;

use Demandify\Domain\Demand\Demand;
use Demandify\Domain\Demand\DemandRepository as DemandRepositoryInterface;
use Demandify\Domain\Demand\Exception\DemandNotFoundException;
use Demandify\Domain\Demand\Status;
use Demandify\Domain\User\User;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;
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

    public function getPaginatedResultForUser(UuidInterface $uuid, int $page, int $limit): iterable
    {
        return $this->createQueryBuilder('d')
            ->where('d.requester = :requester')
            ->setParameter('requester', $uuid)
            ->setFirstResult(($page - 1) * $limit)
            ->setMaxResults($limit)
            ->getQuery()
            ->getResult()
        ;
    }

    public function save(Demand $demand): void
    {
        $demand->updatedAt = new \DateTimeImmutable();
        $this->getEntityManager()->persist($demand);
        $this->getEntityManager()->flush();
    }
}
