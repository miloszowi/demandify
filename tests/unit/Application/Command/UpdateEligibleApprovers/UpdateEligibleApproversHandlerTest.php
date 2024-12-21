<?php

declare(strict_types=1);

namespace unit\Application\Command\UpdateEligibleApprovers;

use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Querify\Application\Command\UpdateEligibleApprovers\UpdateEligibleApprovers;
use Querify\Application\Command\UpdateEligibleApprovers\UpdateEligibleApproversHandler;
use Querify\Domain\ExternalService\ExternalServiceConfiguration;
use Querify\Domain\ExternalService\ExternalServiceConfigurationRepository;
use Ramsey\Uuid\Uuid;

/**
 * @internal
 */
#[CoversClass(UpdateEligibleApproversHandler::class)]
final class UpdateEligibleApproversHandlerTest extends TestCase
{
    private ExternalServiceConfigurationRepository|MockObject $externalServiceConfigurationRepositoryMock;
    private UpdateEligibleApproversHandler $updateEligibleApproversHandler;

    protected function setUp(): void
    {
        $this->externalServiceConfigurationRepositoryMock = $this->createMock(ExternalServiceConfigurationRepository::class);
        $this->updateEligibleApproversHandler = new UpdateEligibleApproversHandler($this->externalServiceConfigurationRepositoryMock);
    }

    public function testUpdatesExistingConfiguration(): void
    {
        $command = new UpdateEligibleApprovers('test_service', [Uuid::uuid4(), Uuid::uuid4()]);
        $externalServiceConfiguration = new ExternalServiceConfiguration($command->externalServiceName, []);

        $this->externalServiceConfigurationRepositoryMock
            ->expects(self::once())
            ->method('findByName')
            ->with($command->externalServiceName)
            ->willReturn($externalServiceConfiguration)
        ;
        $this->externalServiceConfigurationRepositoryMock
            ->expects(self::once())
            ->method('save')
            ->with($externalServiceConfiguration)
        ;

        $this->updateEligibleApproversHandler->__invoke($command);
    }

    public function testCreatesNewConfigurationWhenNotFound(): void
    {
        $command = new UpdateEligibleApprovers('test_service', [Uuid::uuid4(), Uuid::uuid4()]);

        $this->externalServiceConfigurationRepositoryMock
            ->expects(self::once())
            ->method('findByName')
            ->with($command->externalServiceName)
            ->willReturn(null)
        ;
        $this->externalServiceConfigurationRepositoryMock
            ->expects(self::once())
            ->method('save')
            ->with(self::isInstanceOf(ExternalServiceConfiguration::class))
        ;

        $this->updateEligibleApproversHandler->__invoke($command);
    }
}
