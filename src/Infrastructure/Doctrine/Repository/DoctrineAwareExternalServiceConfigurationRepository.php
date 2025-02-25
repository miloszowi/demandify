<?php

declare(strict_types=1);

namespace Demandify\Infrastructure\Doctrine\Repository;

use Demandify\Domain\ExternalService\Exception\ExternalServiceConfigurationNotFoundException;
use Demandify\Domain\ExternalService\ExternalServiceConfiguration;
use Demandify\Domain\ExternalService\ExternalServiceConfigurationRepository;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

class DoctrineAwareExternalServiceConfigurationRepository extends ServiceEntityRepository implements ExternalServiceConfigurationRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, ExternalServiceConfiguration::class);
    }

    public function save(ExternalServiceConfiguration $configuration): void
    {
        $this->getEntityManager()->persist($configuration);
        $this->getEntityManager()->flush();
    }

    public function findByName(string $name): ?ExternalServiceConfiguration
    {
        return $this->createQueryBuilder('esc')
            ->andWhere('esc.externalServiceName = :name')
            ->setParameter('name', $name)
            ->getQuery()
            ->getOneOrNullResult()
        ;
    }

    public function getByName(string $name): ExternalServiceConfiguration
    {
        $configuration = $this->findByName($name);

        if (null === $configuration) {
            throw ExternalServiceConfigurationNotFoundException::fromName($name);
        }

        return $configuration;
    }
}
