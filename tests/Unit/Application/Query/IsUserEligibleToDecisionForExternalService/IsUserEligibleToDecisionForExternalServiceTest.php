<?php

declare(strict_types=1);

namespace Demandify\Tests\Unit\Application\Query\IsUserEligibleToDecisionForExternalService;

use Demandify\Application\Query\IsUserEligibleToDecisionForExternalService\IsUserEligibleToDecisionForExternalService;
use Demandify\Domain\User\User;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;

/**
 * @internal
 */
#[CoversClass(IsUserEligibleToDecisionForExternalService::class)]
final class IsUserEligibleToDecisionForExternalServiceTest extends TestCase
{
    public function testIsInitializable(): void
    {
        $query = new IsUserEligibleToDecisionForExternalService(
            $this->createMock(User::class),
            'test-service-name'
        );

        self::assertInstanceOf(IsUserEligibleToDecisionForExternalService::class, $query);
    }
}
