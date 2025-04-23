<?php

declare(strict_types=1);

namespace Demandify\Tests\Unit\Domain\Notification\Exception;

use Demandify\Domain\Notification\Exception\NotificationNotFoundException;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;
use Ramsey\Uuid\Uuid;

/**
 * @internal
 */
#[CoversClass(NotificationNotFoundException::class)]
final class NotificationNotFoundExceptionTest extends TestCase
{
    public function testItCreatesExceptionWithCorrectMessage(): void
    {
        $uuid = Uuid::uuid4();

        $exception = NotificationNotFoundException::fromDemandUuid($uuid);

        self::assertInstanceOf(NotificationNotFoundException::class, $exception);
        self::assertSame(
            \sprintf('Notification for demand with uuid of "%s" was not found.', $uuid->toString()),
            $exception->getMessage()
        );
    }
}
