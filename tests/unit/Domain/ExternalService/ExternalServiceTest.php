<?php

declare(strict_types=1);

namespace Demandify\Tests\Unit\Domain\ExternalService;

use Demandify\Domain\ExternalService\ExternalServiceConfiguration;
use Demandify\Domain\User\Email;
use Demandify\Domain\User\User;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;
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
        $this->eligibleUser = new User(Email::fromString('eligible.user@local.host'));
        $this->eligibleApproverUuid = $this->eligibleUser->uuid;
        $this->ineligibleUser = new User(Email::fromString('ineligible.user@local.host'));

        $this->externalServiceConfiguration = new ExternalServiceConfiguration(
            'TestService',
            [$this->eligibleApproverUuid->toString()]
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
