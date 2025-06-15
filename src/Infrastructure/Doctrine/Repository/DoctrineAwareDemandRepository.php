<?php

declare(strict_types=1);

namespace Demandify\Infrastructure\Doctrine\Repository;

use Demandify\Domain\Demand\Demand;
use Demandify\Domain\Demand\DemandRepository as DemandRepositoryInterface;
use Demandify\Domain\Demand\Exception\DemandNotFoundException;
use Demandify\Domain\Demand\Status;
use Demandify\Domain\ExternalService\ExternalServiceConfiguration;
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

    public function save(Demand $demand): void
    {
        $demand->updatedAt = new \DateTimeImmutable();
        $this->getEntityManager()->persist($demand);
        $this->getEntityManager()->flush();
    }

    /**
     * @return array{demands: Demand[], total: int, page: int, limit: int, totalPages: int, search: ?string}
     */
    public function findPaginatedForUser(UuidInterface $uuid, int $page, int $limit, ?string $search = null): array
    {
        $countQb = $this->createQueryBuilder('d')
            ->select('COUNT(d.uuid)')
            ->andWhere('d.requester = :requester')
            ->setParameter('requester', $uuid)
        ;

        $qb = $this->createQueryBuilder('d')
            ->leftJoin('d.requester', 'requester')
            ->addSelect('requester')
            ->leftJoin('d.approver', 'approver')
            ->addSelect('approver')
            ->andWhere('d.requester = :requester')
            ->setParameter('requester', $uuid)
            ->orderBy('d.createdAt', 'DESC')
        ;

        if ($search) {
            $searchCondition = $qb->expr()->orX(
                $qb->expr()->like('d.content', ':search'),
                $qb->expr()->like('d.reason', ':search'),
                $qb->expr()->like('d.service', ':search')
            );
            $qb->andWhere($searchCondition);
            $countQb->andWhere($searchCondition);
            $qb->setParameter('search', '%'.$search.'%');
            $countQb->setParameter('search', '%'.$search.'%');
        }

        $total = (int) $countQb->getQuery()->getSingleScalarResult();

        $demands = $qb->setFirstResult(($page - 1) * $limit)
            ->select('d.uuid', 'd.service', 'd.content', 'd.reason', 'd.status', 'd.createdAt')
            ->setMaxResults($limit)
            ->getQuery()
            ->getResult()
        ;

        return [
            'demands' => $demands,
            'total' => $total,
            'page' => $page,
            'limit' => $limit,
            'totalPages' => (int) ceil($total / $limit),
            'search' => $search,
        ];
    }

    /**
     * @param ExternalServiceConfiguration[] $services
     *
     * @return Demand[]
     */
    public function findDemandsAwaitingDecisionForServices(UuidInterface $userUuid, array $services): array
    {
        $serviceNames = array_map(static fn (ExternalServiceConfiguration $service) => $service->externalServiceName, $services);

        return $this->createQueryBuilder('d')
            ->andWhere('d.service IN (:services)')
            ->andWhere('d.approver IS NULL')
            ->andWhere('d.status = :status')
            ->setParameter('services', $serviceNames)
            ->setParameter('status', Status::NEW)
            ->getQuery()
            ->getResult()
        ;
    }
}
