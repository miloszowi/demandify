<?php

declare(strict_types=1);

namespace Demandify\Tests\Unit\Domain\Demand\Exception;

use Demandify\Domain\Demand\Exception\UserNotAuthorizedToUpdateDemandException;
use Demandify\Domain\User\Email;
use Demandify\Domain\User\User;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;

/**
 * @internal
 */
#[CoversClass(UserNotAuthorizedToUpdateDemandException::class)]
final class UserNotAuthorizedToUpdateDemandExceptionTest extends TestCase
{
    public function testItCreatesExceptionWithCorrectMessage(): void
    {
        $user = new User(Email::fromString('user@local.host'));

        $service = 'test-service';
        $exception = UserNotAuthorizedToUpdateDemandException::fromUser($user, $service);

        self::assertInstanceOf(UserNotAuthorizedToUpdateDemandException::class, $exception);
        self::assertSame(
            'User "user@local.host" is not privileged to accept/decline demand for service "test-service".',
            $exception->getMessage()
        );
    }
}
