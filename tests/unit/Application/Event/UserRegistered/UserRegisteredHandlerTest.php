<?php

declare(strict_types=1);

namespace Demandify\Tests\Unit\Application\Event\UserRegistered;

use Demandify\Application\Event\UserRegistered\UserRegisteredHandler;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;

/**
 * @internal
 */
#[CoversClass(UserRegisteredHandler::class)]
final class UserRegisteredHandlerTest extends TestCase
{
    public function testSomething(): void
    {
        self::markTestIncomplete('This test has not been implemented yet.');
    }
}
