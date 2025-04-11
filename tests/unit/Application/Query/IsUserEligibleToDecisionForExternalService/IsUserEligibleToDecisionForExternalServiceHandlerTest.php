<?php

declare(strict_types=1);

namespace Demandify\Tests\Unit\Application\Query\IsUserEligibleToDecisionForExternalService;

use Demandify\Application\Query\IsUserEligibleToDecisionForExternalService\IsUserEligibleToDecisionForExternalService;
use Demandify\Application\Query\IsUserEligibleToDecisionForExternalService\IsUserEligibleToDecisionForExternalServiceHandler;
use Demandify\Domain\ExternalService\ExternalServiceConfiguration;
use Demandify\Domain\ExternalService\ExternalServiceConfigurationRepository;
use Demandify\Domain\User\User;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

/**
 * @internal
 */
#[CoversClass(IsUserEligibleToDecisionForExternalServiceHandler::class)]
final class IsUserEligibleToDecisionForExternalServiceHandlerTest extends TestCase
{
    private IsUserEligibleToDecisionForExternalServiceHandler $handler;
    private ExternalServiceConfigurationRepository|MockObject $externalServiceConfigurationRepository;

    protected function setUp(): void
    {
        $this->externalServiceConfigurationRepository = $this->createMock(ExternalServiceConfigurationRepository::class);
        $this->handler = new IsUserEligibleToDecisionForExternalServiceHandler($this->externalServiceConfigurationRepository);
    }

    public function testItReturnsTrueIfUserIsEligibleToDecisionForExternalService(): void
    {
        $query = new IsUserEligibleToDecisionForExternalService(
            $this->createMock(User::class),
            'demandify_postgres'
        );
        $mockExternalServiceConfiguration = $this->createMock(ExternalServiceConfiguration::class);
        $mockExternalServiceConfiguration
            ->expects(self::once())
            ->method('isUserEligible')
            ->willReturn(true)
        ;

        $this->externalServiceConfigurationRepository
            ->expects(self::once())
            ->method('getByName')
            ->with($query->externalServiceName)
            ->willReturn($mockExternalServiceConfiguration)
        ;

        $result = $this->handler->__invoke($query);

        self::assertTrue($result);
    }

    public function testItReturnsFalseIfUserIsNotEligibleToDecisionForExternalService(): void
    {
        $query = new IsUserEligibleToDecisionForExternalService(
            $this->createMock(User::class),
            'demandify_postgres'
        );
        $mockExternalServiceConfiguration = $this->createMock(ExternalServiceConfiguration::class);
        $mockExternalServiceConfiguration
            ->expects(self::once())
            ->method('isUserEligible')
            ->willReturn(false)
        ;

        $this->externalServiceConfigurationRepository
            ->expects(self::once())
            ->method('getByName')
            ->with($query->externalServiceName)
            ->willReturn($mockExternalServiceConfiguration)
        ;

        $result = $this->handler->__invoke($query);

        self::assertFalse($result);
    }
}
