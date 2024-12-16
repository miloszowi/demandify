<?php

declare(strict_types=1);

namespace Querify\Infrastructure\Doctrine\Repository;

use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;
use Querify\Domain\User\Email;
use Querify\Domain\User\Exception\UserNotFoundException;
use Querify\Domain\User\User;
use Querify\Domain\User\UserRepository as UserRepositoryInterface;
use Ramsey\Uuid\UuidInterface;

class DoctrineAwareUserRepository extends ServiceEntityRepository implements UserRepositoryInterface
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, User::class);
    }

    public function findByUuid(UuidInterface $uuid): ?User
    {
        return $this->createQueryBuilder('u')
            ->andWhere('u.uuid = :uuid')
            ->setParameter('uuid', $uuid->toString())
            ->getQuery()
            ->getOneOrNullResult()
        ;
    }

    public function getByUuid(UuidInterface $uuid): User
    {
        $user = $this->findByUuid($uuid);

        if (null === $user) {
            throw UserNotFoundException::fromUuid($uuid);
        }

        return $user;
    }

    public function findByEmail(Email $email): ?User
    {
        return $this->createQueryBuilder('u')
            ->andWhere('u.email.email = :email')
            ->setParameter('email', (string) $email)
            ->getQuery()
            ->getOneOrNullResult()
        ;
    }

    public function getByEmail(Email $email): User
    {
        $user = $this->findByEmail($email);

        if (null === $user) {
            throw UserNotFoundException::fromEmail($email);
        }

        return $user;
    }

    public function save(User $user): void
    {
        $this->getEntityManager()->persist($user);
        $this->getEntityManager()->flush();
    }

    public function getAll(): array
    {
        return $this->findAll();
    }

    /**
     * @return UuidInterface[]
     */
    public function findByUuids(array $uuids): array
    {
        return $this->createQueryBuilder('u')
            ->andWhere('u.uuid IN (:uuids)')
            ->setParameter(':uuids', $uuids)
            ->getQuery()
            ->getResult()
        ;
    }
}
