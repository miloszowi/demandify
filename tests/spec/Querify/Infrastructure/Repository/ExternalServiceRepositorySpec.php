<?php

declare(strict_types=1);

namespace spec\Querify\Infrastructure\Repository;

use PhpSpec\ObjectBehavior;
use Querify\Domain\ExternalService\Exception\ExternalServiceNotFoundException;
use Querify\Domain\ExternalService\ExternalServiceType;
use Querify\Infrastructure\ExternalService\Exception\InvalidExternalServiceConfiguration;
use Symfony\Component\Serializer\Encoder\JsonEncoder;
use Symfony\Component\Serializer\Normalizer\ObjectNormalizer;
use Symfony\Component\Serializer\Serializer;

class ExternalServiceRepositorySpec extends ObjectBehavior
{
    public function let(): void
    {
        $serializer = new Serializer([new ObjectNormalizer()], [new JsonEncoder()]);
        $this->beConstructedWith($serializer);
        $_ENV['EXTERNAL_SERVICE__querify_postgres'] = '{
            "type": "postgres",
            "name": "querify",
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
    }

    public function it_returns_external_service_from_the_environment_variable(): void
    {
        $externalService = $this->getByName('querify_postgres');

        $externalService->type->shouldBe(ExternalServiceType::POSTGRES);
        $externalService->name->shouldBe('querify_postgres');
        $externalService->serviceName->shouldBe('querify');
        $externalService->host->shouldBe('localhost');
        $externalService->user->shouldBe('postgres');
        $externalService->password->shouldBe('postgres');
        $externalService->port->shouldBe(5432);
    }

    public function it_throw_exception_when_trying_to_get_non_existing_external_service(): void
    {
        self::shouldThrow(ExternalServiceNotFoundException::class)->during(
            'getByName',
            ['non_existing_external_service']
        );
    }

    public function it_get_all_external_services(): void
    {
        $this->getAll()->shouldHaveCount(2);
    }

    public function it_throws_exception_when_configuration_is_invalid(): void
    {
        $_ENV['EXTERNAL_SERVICE__invalid_service'] = 'not a valid json';
        $serializer = new Serializer([new ObjectNormalizer()], [new JsonEncoder()]);

        $this->shouldThrow(InvalidExternalServiceConfiguration::class)->during(
            '__construct',
            [$serializer],
        );
    }
}
