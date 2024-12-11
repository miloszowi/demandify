<?php

declare(strict_types=1);

namespace Querify\Infrastructure\Repository;

use Querify\Domain\ExternalService\Exception\ExternalServiceNotFoundException;
use Querify\Domain\ExternalService\ExternalService;
use Querify\Domain\ExternalService\ExternalServiceRepository as ExternalRepositoryInterface;
use Querify\Domain\ExternalService\ExternalServiceType;
use Querify\Infrastructure\ExternalService\Exception\InvalidExternalServiceConfiguration;
use Querify\Infrastructure\ExternalService\ExternalServiceEnvDTO;
use Symfony\Component\Serializer\Encoder\JsonEncoder;
use Symfony\Component\Serializer\SerializerInterface;

class ExternalServiceRepository implements ExternalRepositoryInterface
{
    /**
     * @var ExternalServiceEnvDTO[]
     */
    private array $externalServices = [];

    public function __construct(private readonly SerializerInterface $serializer)
    {
        foreach ($_ENV as $key => $value) {
            if (str_starts_with($key, 'EXTERNAL_SERVICE__')) {
                $normalizedKey = substr($key, \strlen('EXTERNAL_SERVICE__'));

                try {
                    $this->externalServices[$normalizedKey] = $this->serializer->deserialize(
                        $value,
                        ExternalServiceEnvDTO::class,
                        JsonEncoder::FORMAT
                    );
                } catch (\Exception $e) {
                    throw InvalidExternalServiceConfiguration::fromValue($value);
                }
            }
        }
    }

    public function getByName(string $serviceName): ExternalService
    {
        foreach ($this->externalServices as $name => $externalService) {
            if ($name === $serviceName) {
                return new ExternalService(
                    ExternalServiceType::from($externalService->type),
                    $name,
                    $externalService->name,
                    $externalService->host,
                    $externalService->user,
                    $externalService->password,
                    $externalService->port
                );
            }
        }

        throw ExternalServiceNotFoundException::fromName($serviceName);
    }

    /**
     * @return ExternalService[]
     */
    public function getAll(): array
    {
        $result = [];

        foreach ($this->externalServices as $name => $externalService) {
            $result[] = new ExternalService(
                ExternalServiceType::from($externalService->type),
                $name,
                $externalService->name,
                $externalService->host,
                $externalService->user,
                $externalService->password,
                $externalService->port
            );
        }

        return $result;
    }
}
