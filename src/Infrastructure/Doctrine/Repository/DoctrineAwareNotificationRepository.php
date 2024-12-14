<?php

declare(strict_types=1);

namespace Querify\Infrastructure\Doctrine\Repository;

use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;
use Querify\Domain\Notification\Exception\NotificationNotFoundException;
use Querify\Domain\Notification\Notification;
use Querify\Domain\Notification\NotificationRepository;
use Ramsey\Uuid\UuidInterface;

class DoctrineAwareNotificationRepository extends ServiceEntityRepository implements NotificationRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Notification::class);
    }

    public function getByDemandUuid(UuidInterface $demandUuid): Notification
    {
        $notification = $this->createQueryBuilder('n')
            ->andWhere('n.demand = :uuid')
            ->setParameter('uuid', $demandUuid->toString())
            ->getQuery()
            ->getOneOrNullResult()
        ;

        if (null === $notification) {
            throw NotificationNotFoundException::fromDemandUuid($demandUuid);
        }

        return $notification;
    }

    public function save(Notification $notification): void
    {
        $this->getEntityManager()->persist($notification);
        $this->getEntityManager()->flush();
    }
}
