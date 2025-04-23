<?php

declare(strict_types=1);

namespace Demandify\Tests\Unit\Application\Command\UpdateSentNotificationsWithDecision;

use Demandify\Application\Command\UpdateSentNotificationsWithDecision\UpdateSentNotificationsWithDecision;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;
use Ramsey\Uuid\Uuid;

/**
 * @internal
 */
#[CoversClass(UpdateSentNotificationsWithDecision::class)]
final class UpdateSentNotificationsWithDecisionTest extends TestCase
{
    public function testIsInitializable(): void
    {
        $command = new UpdateSentNotificationsWithDecision(Uuid::uuid4());

        self::assertInstanceOf(UpdateSentNotificationsWithDecision::class, $command);
    }
}
