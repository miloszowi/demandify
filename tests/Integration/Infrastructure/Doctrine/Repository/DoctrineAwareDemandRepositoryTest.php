<?php

declare(strict_types=1);

namespace Demandify\Tests\Integration\Infrastructure\Doctrine\Repository;

use Demandify\Domain\Demand\Demand;
use Demandify\Domain\Demand\Exception\DemandNotFoundException;
use Demandify\Infrastructure\Doctrine\Repository\DoctrineAwareDemandRepository;
use Demandify\Tests\Fixtures\DemandFixture;
use Demandify\Tests\Integration\BaseKernelTestCase;
use PHPUnit\Framework\Attributes\CoversClass;
use Ramsey\Uuid\Uuid;

/**
 * @internal
 */
#[CoversClass(DoctrineAwareDemandRepository::class)]
final class DoctrineAwareDemandRepositoryTest extends BaseKernelTestCase
{
    private DoctrineAwareDemandRepository $repository;

    protected function setUp(): void
    {
        $this->repository = self::getContainer()->get(DoctrineAwareDemandRepository::class);

        $this->load([new DemandFixture()]);
    }

    public function testGetByUuid(): void
    {
        $uuid = Uuid::fromString(
            self::getEntityManager()->getConnection()->fetchOne('SELECT uuid FROM demand')
        );

        $demand = $this->repository->getByUuid($uuid);

        self::assertInstanceOf(Demand::class, $demand);
        self::assertTrue($uuid->equals($demand->uuid));
    }

    public function testGetByUuidThrowsException(): void
    {
        $nonExistingUuid = Uuid::uuid4();

        self::expectException(DemandNotFoundException::class);
        self::expectExceptionMessage(
            \sprintf('Demand with uuid of "%s" was not found.', $nonExistingUuid->toString())
        );

        $this->repository->getByUuid($nonExistingUuid);
    }

    public function testFindByUuid(): void
    {
        $uuid = Uuid::fromString(
            self::getEntityManager()->getConnection()->fetchOne('SELECT uuid FROM demand')
        );

        $demand = $this->repository->findByUuid($uuid);

        self::assertInstanceOf(Demand::class, $demand);
        self::assertTrue($uuid->equals($demand->uuid));
    }

    public function testFindsByUuidNonExistingDemandReturnsNull(): void
    {
        $nonExistingUuid = Uuid::uuid4();

        $demand = $this->repository->findByUuid($nonExistingUuid);

        self::assertNull($demand);
    }
}
