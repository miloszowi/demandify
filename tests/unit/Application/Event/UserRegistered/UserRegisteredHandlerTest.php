<?php

declare(strict_types=1);

namespace Querify\Tests\Unit\Application\Event\UserRegistered;

use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;
use Querify\Application\Event\UserRegistered\UserRegisteredHandler;

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
