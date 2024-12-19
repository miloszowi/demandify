<?php

declare(strict_types=1);

namespace Querify\Tests\Application\Event\DemandSubmitted;

use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Querify\Application\Event\DemandSubmitted\DemandSubmittedHandler;
use Querify\Domain\Demand\Demand;
use Querify\Domain\Demand\Event\DemandSubmitted;
use Querify\Domain\ExternalService\ExternalServiceConfiguration;
use Querify\Domain\ExternalService\ExternalServiceConfigurationRepository;
use Querify\Domain\User\User;
use Ramsey\Uuid\Uuid;
use Symfony\Component\Messenger\Envelope;
use Symfony\Component\Messenger\MessageBusInterface;

/**
 * @internal
 */
#[CoversClass(DemandSubmittedHandler::class)]
final class DemandSubmittedHandlerTest extends TestCase
{
    private ExternalServiceConfigurationRepository|MockObject $externalServiceConfigurationRepositoryMock;
    private MessageBusInterface|MockObject $messageBusMock;
    private DemandSubmittedHandler $handler;

    protected function setUp(): void
    {
        $this->externalServiceConfigurationRepositoryMock = $this->createMock(ExternalServiceConfigurationRepository::class);
        $this->messageBusMock = $this->createMock(MessageBusInterface::class);
        $this->handler = new DemandSubmittedHandler(
            $this->externalServiceConfigurationRepositoryMock,
            $this->messageBusMock
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

        $this->messageBusMock
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

        $mockEnvelope = new Envelope(new \stdClass());
        $this->messageBusMock
            ->expects(self::exactly(2))
            ->method('dispatch')
            ->withAnyParameters()
            ->willReturnOnConsecutiveCalls($mockEnvelope, $mockEnvelope)
        ;

        $this->handler->__invoke($event);
    }
}
