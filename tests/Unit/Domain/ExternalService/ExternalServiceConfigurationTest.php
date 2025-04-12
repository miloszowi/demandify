<?php

declare(strict_types=1);

namespace Demandify\Tests\Unit\Domain\ExternalService;

use Demandify\Domain\ExternalService\ExternalServiceConfiguration;
use Demandify\Domain\User\Email;
use Demandify\Domain\User\User;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;

/**
 * @internal
 */
#[CoversClass(ExternalServiceConfiguration::class)]
final class ExternalServiceConfigurationTest extends TestCase
{
    public function testWillReturnTrueIfUserIsEligible(): void
    {
        $user = new User(
            Email::fromString('user@local.host')
        );

        $externalServiceConfiguration = new ExternalServiceConfiguration(
            'TestService',
            [$user->uuid->toString()]
        );

        $result = $externalServiceConfiguration->isUserEligible($user);

        self::assertTrue($result);
    }

    public function testWillReturnFalseIfUserIsNotEligible(): void
    {
        $user = new User(
            Email::fromString('user@local.host')
        );

        $externalServiceConfiguration = new ExternalServiceConfiguration(
            'TestService',
            []
        );

        $result = $externalServiceConfiguration->isUserEligible($user);

        self::assertFalse($result);
    }
}
