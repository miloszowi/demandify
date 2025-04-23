<?php

declare(strict_types=1);

namespace Demandify\Tests\Unit\Application\Command\UpdateSocialAccountNotifiability;

use Demandify\Application\Command\UpdateSocialAccountNotifiability\UpdateSocialAccountNotifiability;
use Demandify\Domain\UserSocialAccount\UserSocialAccountType;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;
use Ramsey\Uuid\Uuid;

/**
 * @internal
 */
#[CoversClass(UpdateSocialAccountNotifiability::class)]
final class UpdateSocialAccountNotifiabilityTest extends TestCase
{
    public function testIsInitializable(): void
    {
        $command = new UpdateSocialAccountNotifiability(
            Uuid::uuid4(),
            UserSocialAccountType::SLACK,
            true
        );

        self::assertInstanceOf(UpdateSocialAccountNotifiability::class, $command);
    }
}
