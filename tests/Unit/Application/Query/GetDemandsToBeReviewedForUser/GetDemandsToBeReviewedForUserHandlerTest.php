<?php

declare(strict_types=1);

namespace Demandify\Tests\Unit\Application\Query\GetDemandsToBeReviewedForUser;

use Demandify\Application\Query\GetDemandsToBeReviewedForUser\GetDemandsToBeReviewedForUser;
use Demandify\Application\Query\GetDemandsToBeReviewedForUser\GetDemandsToBeReviewedForUserHandler;
use Demandify\Domain\Demand\Demand;
use Demandify\Domain\Demand\DemandRepository;
use Demandify\Domain\ExternalService\ExternalServiceConfiguration;
use Demandify\Domain\ExternalService\ExternalServiceConfigurationRepository;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Ramsey\Uuid\Uuid;

/**
 * @internal
 */
#[CoversClass(GetDemandsToBeReviewedForUserHandler::class)]
final class GetDemandsToBeReviewedForUserHandlerTest extends TestCase
{
    private GetDemandsToBeReviewedForUserHandler $handler;
    private DemandRepository|MockObject $demandRepository;
    private ExternalServiceConfigurationRepository|MockObject $externalServiceConfigurationRepository;

    protected function setUp(): void
    {
        $this->demandRepository = $this->createMock(DemandRepository::class);
        $this->externalServiceConfigurationRepository = $this->createMock(ExternalServiceConfigurationRepository::class);
        $this->handler = new GetDemandsToBeReviewedForUserHandler(
            $this->demandRepository,
            $this->externalServiceConfigurationRepository,
        );
    }

    public function testItReturnsAllDemandsToBeReviewedForUser(): void
    {
        $userUuid = Uuid::uuid4();
        $eligibleServices = [
            new ExternalServiceConfiguration(
                'demandify_postgres',
                [$userUuid->toString()]
            ),
        ];

        $this->externalServiceConfigurationRepository
            ->expects(self::once())
            ->method('findForUser')
            ->with($userUuid)
            ->willReturn($eligibleServices)
        ;

        $this->demandRepository
            ->expects(self::once())
            ->method('findDemandsAwaitingDecisionForServices')
            ->with($userUuid, $eligibleServices)
            ->willReturn([
                $this->createMock(Demand::class),
            ])
        ;

        $query = new GetDemandsToBeReviewedForUser($userUuid);
        $this->handler->__invoke($query);
    }
}
