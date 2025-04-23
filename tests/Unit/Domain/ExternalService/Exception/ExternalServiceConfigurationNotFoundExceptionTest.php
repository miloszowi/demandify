<?php

declare(strict_types=1);

namespace Demandify\Tests\Unit\Domain\ExternalService\Exception;

use Demandify\Domain\ExternalService\Exception\ExternalServiceConfigurationNotFoundException;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;

/**
 * @internal
 */
#[CoversClass(ExternalServiceConfigurationNotFoundException::class)]
final class ExternalServiceConfigurationNotFoundExceptionTest extends TestCase
{
    public function testItCreatesExceptionWithCorrectMessage(): void
    {
        $serviceName = 'test-service';
        $exception = ExternalServiceConfigurationNotFoundException::fromName($serviceName);

        self::assertInstanceOf(ExternalServiceConfigurationNotFoundException::class, $exception);
        self::assertSame(
            \sprintf('External service "%s" configuration was not found', $serviceName),
            $exception->getMessage()
        );
    }
}
