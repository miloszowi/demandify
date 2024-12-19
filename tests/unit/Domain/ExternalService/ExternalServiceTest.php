<?php

declare(strict_types=1);

namespace Querify\Tests\Unit\Domain\ExternalService;

use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;
use Querify\Domain\ExternalService\ExternalServiceConfiguration;
use Querify\Domain\User\Email;
use Querify\Domain\User\User;
use Ramsey\Uuid\UuidInterface;

/**
 * @internal
 */
#[CoversClass(ExternalServiceConfiguration::class)]
final class ExternalServiceTest extends TestCase
{
    private ExternalServiceConfiguration $externalServiceConfiguration;
    private UuidInterface $eligibleApproverUuid;
    private User $eligibleUser;
    private User $ineligibleUser;

    protected function setUp(): void
    {
        $this->eligibleUser = new User(
            Email::fromString('eligible.user@local.host'),
            'test name',
        );
        $this->eligibleApproverUuid = $this->eligibleUser->uuid;
        $this->ineligibleUser = new User(
            Email::fromString('ineligible.user@local.host'),
            'test name',
        );

        $this->externalServiceConfiguration = new ExternalServiceConfiguration(
            'TestService',
            [$this->eligibleApproverUuid]
        );
    }

    public function testIsUserEligibleReturnsTrueForEligibleUser(): void
    {
        self::assertTrue($this->externalServiceConfiguration->isUserEligible($this->eligibleUser));
    }

    public function testIsUserEligibleReturnsFalseForIneligibleUser(): void
    {
        self::assertFalse($this->externalServiceConfiguration->isUserEligible($this->ineligibleUser));
    }
}
