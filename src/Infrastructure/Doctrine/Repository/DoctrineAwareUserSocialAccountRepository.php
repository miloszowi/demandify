<?php

declare(strict_types=1);

namespace Querify\Infrastructure\Doctrine\Repository;

use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\ORM\Query\Expr\Join;
use Doctrine\Persistence\ManagerRegistry;
use Querify\Domain\User\Email;
use Querify\Domain\User\User;
use Querify\Domain\UserSocialAccount\Exception\UserSocialAccountNotFound;
use Querify\Domain\UserSocialAccount\UserSocialAccount;
use Querify\Domain\UserSocialAccount\UserSocialAccountRepository as UserSocialAccountRepositoryInterface;
use Querify\Domain\UserSocialAccount\UserSocialAccountType;
use Ramsey\Uuid\UuidInterface;

class DoctrineAwareUserSocialAccountRepository extends ServiceEntityRepository implements UserSocialAccountRepositoryInterface
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, UserSocialAccount::class);
    }

    public function findByUserUuidAndType(UuidInterface $userUuid, UserSocialAccountType $userSocialAccountType): ?UserSocialAccount
    {
        return $this->createQueryBuilder('usa')
            ->andWhere('usa.userUuid = :user_uuid')
            ->andWhere('usa.type = :type')
            ->setParameter(':user_uuid', $userUuid->toString())
            ->setParameter(':type', $userSocialAccountType->value)
            ->getQuery()
            ->getOneOrNullResult()
        ;
    }

    public function getByExternalIdAndType(string $externalId, UserSocialAccountType $userSocialAccountType): ?UserSocialAccount
    {
        $userSocialAccount = $this->createQueryBuilder('usa')
            ->andWhere('usa.externalId = :user_uuid')
            ->andWhere('usa.type = :type')
            ->setParameter(':user_uuid', $externalId)
            ->setParameter(':type', $userSocialAccountType->value)
            ->getQuery()
            ->getOneOrNullResult()
        ;

        if (null === $userSocialAccount) {
            throw UserSocialAccountNotFound::fromExternalIdAndType($externalId, $userSocialAccountType);
        }

        return $userSocialAccount;
    }

    public function findByEmailAndType(Email $email, UserSocialAccountType $userSocialAccountType): ?UserSocialAccount
    {
        return $this->createQueryBuilder('usa')
            ->leftJoin(
                User::class,
                'u',
                Join::WITH,
                'usa.user = u.uuid'
            )
            ->where('u.email.email = :email')
            ->andWhere('usa.type = :type')
            ->setParameter(':email', (string) $email)
            ->setParameter(':type', $userSocialAccountType->value)
            ->getQuery()
            ->getOneOrNullResult()
        ;
    }

    public function save(UserSocialAccount $userSocialAccount): void
    {
        $this->getEntityManager()->persist($userSocialAccount);
        $this->getEntityManager()->flush();
    }
}
