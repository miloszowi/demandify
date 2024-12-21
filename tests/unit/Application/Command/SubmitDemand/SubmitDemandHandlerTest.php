<?php

declare(strict_types=1);

namespace Tests\Querify\Application\Command\SubmitDemand;

use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Querify\Application\Command\SubmitDemand\SubmitDemand;
use Querify\Application\Command\SubmitDemand\SubmitDemandHandler;
use Querify\Domain\Demand\Demand;
use Querify\Domain\Demand\DemandRepository;
use Querify\Domain\Demand\Event\DemandSubmitted;
use Querify\Domain\DomainEventPublisher;
use Querify\Domain\User\Email;
use Querify\Domain\User\User;
use Querify\Domain\User\UserRepository;

/**
 * @internal
 */
#[CoversClass(SubmitDemandHandler::class)]
final class SubmitDemandHandlerTest extends TestCase
{
    private DemandRepository|MockObject $demandRepositoryMock;
    private DomainEventPublisher|MockObject $domainEventPublisherMock;
    private MockObject|UserRepository $userRepositoryMock;
    private SubmitDemandHandler $submitDemandHandler;

    protected function setUp(): void
    {
        $this->demandRepositoryMock = $this->createMock(DemandRepository::class);
        $this->domainEventPublisherMock = $this->createMock(DomainEventPublisher::class);
        $this->userRepositoryMock = $this->createMock(UserRepository::class);
        $this->submitDemandHandler = new SubmitDemandHandler($this->demandRepositoryMock, $this->domainEventPublisherMock, $this->userRepositoryMock);
    }

    public function testSubmitsDemand(): void
    {
        $userMock = $this->createMock(User::class);
        $command = new SubmitDemand(
            'example@local.host',
            'example-service',
            'content',
            'reason'
        );

        $this->userRepositoryMock
            ->expects(self::once())
            ->method('getByEmail')
            ->with(Email::fromString($command->requesterEmail))->willReturn($userMock)
        ;
        $this->demandRepositoryMock
            ->expects(self::once())
            ->method('save')
            ->with(self::isInstanceOf(Demand::class))
        ;
        $this->domainEventPublisherMock
            ->expects(self::once())
            ->method('publish')
            ->with(self::isInstanceOf(DemandSubmitted::class))
        ;

        $this->submitDemandHandler->__invoke($command);
    }
}
