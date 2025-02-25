<?php

declare(strict_types=1);

namespace Demandify\Tests\Unit\Infrastructure\Repository;

use Demandify\Domain\ExternalService\Exception\ExternalServiceNotFoundException;
use Demandify\Domain\ExternalService\ExternalServiceType;
use Demandify\Infrastructure\Repository\EnvAwareExternalServiceRepository;
use Demandify\Infrastructure\Repository\ExternalService\Exception\InvalidExternalServiceConfiguration;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Serializer\Encoder\JsonEncoder;
use Symfony\Component\Serializer\Normalizer\ObjectNormalizer;
use Symfony\Component\Serializer\Serializer;

/**
 * @internal
 */
#[CoversClass(EnvAwareExternalServiceRepository::class)]
final class EnvAwareExternalServiceRepositoryTest extends TestCase
{
    private EnvAwareExternalServiceRepository $repository;

    protected function setUp(): void
    {
        $_ENV['EXTERNAL_SERVICE__demandify_postgres'] = '{
            "type": "postgres",
            "name": "demandify",
            "host": "localhost",
            "user": "postgres",
            "password": "postgres",
            "port": 5432
        }';
        $_ENV['EXTERNAL_SERVICE__some_mariadb'] = '{
            "type": "mariadb",
            "name": "some_app",
            "host": "localhost",
            "user": "mariadb_user",
            "password": "mariadb_password",
            "port": 3306
        }';

        $serializer = new Serializer([new ObjectNormalizer()], [new JsonEncoder()]);
        $this->repository = new EnvAwareExternalServiceRepository($serializer);
    }

    public function testReturnsExternalServiceFromEnvironmentVariable(): void
    {
        $externalService = $this->repository->getByName('demandify_postgres');

        self::assertSame(ExternalServiceType::POSTGRES, $externalService->type);
        self::assertSame('demandify_postgres', $externalService->name);
        self::assertSame('demandify', $externalService->serviceName);
        self::assertSame('localhost', $externalService->host);
        self::assertSame('postgres', $externalService->user);
        self::assertSame('postgres', $externalService->password);
        self::assertSame(5432, $externalService->port);
    }

    public function testThrowsExceptionWhenTryingToGetNonExistingExternalService(): void
    {
        $this->expectException(ExternalServiceNotFoundException::class);
        $this->repository->getByName('non_existing_external_service');
    }

    public function testGetAllExternalServices(): void
    {
        $externalServices = $this->repository->getAll();
        self::assertCount(2, $externalServices);
    }

    public function testThrowsExceptionWhenConfigurationIsInvalid(): void
    {
        $_ENV['EXTERNAL_SERVICE__invalid_service'] = 'not a valid json';
        $serializer = new Serializer([new ObjectNormalizer()], [new JsonEncoder()]);

        $this->expectException(InvalidExternalServiceConfiguration::class);

        try {
            new EnvAwareExternalServiceRepository($serializer);
        } catch (InvalidExternalServiceConfiguration $e) {
            unset($_ENV['EXTERNAL_SERVICE__invalid_service']);

            throw $e;
        }
    }

    public function testHandlesEmptyEnvironmentVariables(): void
    {
        $_ENV = [];
        $serializer = new Serializer([new ObjectNormalizer()], [new JsonEncoder()]);
        $repository = new EnvAwareExternalServiceRepository($serializer);

        $externalServices = $repository->getAll();
        self::assertCount(0, $externalServices);
    }
}
