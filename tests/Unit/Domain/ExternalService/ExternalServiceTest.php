<?php

declare(strict_types=1);

namespace Demandify\Tests\Unit\Domain\ExternalService;

use Demandify\Domain\ExternalService\ExternalService;
use Demandify\Domain\ExternalService\ExternalServiceType;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;

/**
 * @internal
 */
#[CoversClass(ExternalService::class)]
final class ExternalServiceTest extends TestCase
{
    public function testItStoresProvidedValues(): void
    {
        $type = ExternalServiceType::POSTGRES;

        $service = new ExternalService(
            type: $type,
            name: 'My External Service',
            serviceName: 'svc-name',
            host: 'localhost',
            user: 'admin',
            password: 'secret',
            port: 8080
        );

        self::assertSame($type, $service->type);
        self::assertSame('My External Service', $service->name);
        self::assertSame('svc-name', $service->serviceName);
        self::assertSame('localhost', $service->host);
        self::assertSame('admin', $service->user);
        self::assertSame('secret', $service->password);
        self::assertSame(8080, $service->port);
    }
}
