<?php

declare(strict_types=1);

namespace Querify\Infrastructure\Doctrine\Repository;

use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;
use Querify\Domain\Notification\Notification;
use Querify\Domain\Notification\NotificationRepository;
use Querify\Domain\Notification\NotificationType;
use Ramsey\Uuid\UuidInterface;

class DoctrineAwareNotificationRepository extends ServiceEntityRepository implements NotificationRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Notification::class);
    }

    public function findByDemandUuidAndAction(UuidInterface $demandUuid, NotificationType $notificationType): array
    {
        return $this->createQueryBuilder('n')
            ->andWhere('n.demandUuid = :uuid')
            ->andWhere('n.type = :type')
            ->setParameter('uuid', $demandUuid->toString())
            ->setParameter('type', $notificationType->value)
            ->getQuery()
            ->getResult()
        ;
    }

    public function findByNotificationIdentifier(string $notificationIdentifier): ?Notification
    {
        return $this->createQueryBuilder('n')
            ->andWhere('n.notificationIdentifier = :identifier')
            ->setParameter('identifier', $notificationIdentifier)
            ->getQuery()
            ->getOneOrNullResult()
        ;
    }

    public function save(Notification $notification): void
    {
        $this->getEntityManager()->persist($notification);
        $this->getEntityManager()->flush();
    }
}
