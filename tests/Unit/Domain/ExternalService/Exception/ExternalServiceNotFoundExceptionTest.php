<?php

declare(strict_types=1);

namespace Demandify\Tests\Unit\Domain\ExternalService\Exception;

use Demandify\Domain\ExternalService\Exception\ExternalServiceNotFoundException;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;

/**
 * @internal
 */
#[CoversClass(ExternalServiceNotFoundException::class)]
final class ExternalServiceNotFoundExceptionTest extends TestCase
{
    public function testItCreatesExceptionWithCorrectMessage(): void
    {
        $serviceName = 'test-service';
        $exception = ExternalServiceNotFoundException::fromName($serviceName);

        self::assertInstanceOf(ExternalServiceNotFoundException::class, $exception);
        self::assertSame(
            \sprintf('External service with name "%s" was not found', $serviceName),
            $exception->getMessage()
        );
    }
}
