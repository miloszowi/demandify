<?php

declare(strict_types=1);

namespace Demandify\Tests\Application\Event\DemandSubmitted;

use Demandify\Application\Command\CommandBus;
use Demandify\Application\Event\DemandSubmitted\DemandSubmittedHandler;
use Demandify\Domain\Demand\Demand;
use Demandify\Domain\Demand\Event\DemandSubmitted;
use Demandify\Domain\ExternalService\ExternalServiceConfiguration;
use Demandify\Domain\ExternalService\ExternalServiceConfigurationRepository;
use Demandify\Domain\User\User;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Ramsey\Uuid\Uuid;

/**
 * @internal
 */
#[CoversClass(DemandSubmittedHandler::class)]
final class DemandSubmittedHandlerTest extends TestCase
{
    private ExternalServiceConfigurationRepository|MockObject $externalServiceConfigurationRepositoryMock;
    private CommandBus|MockObject $commandBusMock;
    private DemandSubmittedHandler $handler;

    protected function setUp(): void
    {
        $this->externalServiceConfigurationRepositoryMock = $this->createMock(ExternalServiceConfigurationRepository::class);
        $this->commandBusMock = $this->createMock(CommandBus::class);
        $this->handler = new DemandSubmittedHandler(
            $this->externalServiceConfigurationRepositoryMock,
            $this->commandBusMock
        );
    }

    public function testHandlingWillNotDispatchAnyMessageDueToNoEligibleApprovers(): void
    {
        $demand = new Demand(
            $this->createMock(User::class),
            'some_service',
            'some_content',
            'some_reason',
        );
        $event = new DemandSubmitted($demand);

        $this->externalServiceConfigurationRepositoryMock
            ->expects(self::once())
            ->method('findByName')
            ->with($demand->service)
            ->willReturn(null)
        ;
        $this->commandBusMock
            ->expects(self::never())
            ->method('dispatch')
        ;

        $this->handler->__invoke($event);
    }

    public function testHandlingWillDispatchTwoMessagesForTwoEligibleApprovers(): void
    {
        $demand = new Demand(
            $this->createMock(User::class),
            'some_service',
            'some_content',
            'some_reason',
        );
        $event = new DemandSubmitted($demand);

        $externalServiceConfiguration = $this->createMock(ExternalServiceConfiguration::class);
        $externalServiceConfiguration->eligibleApprovers = [Uuid::uuid4(), Uuid::uuid4()];

        $this->externalServiceConfigurationRepositoryMock
            ->expects(self::once())
            ->method('findByName')
            ->with($demand->service)
            ->willReturn($externalServiceConfiguration)
        ;

        $this->commandBusMock
            ->expects(self::exactly(2))
            ->method('dispatch')
            ->withAnyParameters()
        ;

        $this->handler->__invoke($event);
    }
}
