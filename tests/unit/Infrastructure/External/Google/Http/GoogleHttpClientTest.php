<?php

declare(strict_types=1);

namespace Demandify\Tests\Unit\Infrastructure\External\Google\Http;

use Demandify\Infrastructure\External\Google\GoogleConfiguration;
use Demandify\Infrastructure\External\Google\Http\GoogleHttpClient;
use Demandify\Infrastructure\External\Http\LoggingAwareHttpClient;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Serializer\SerializerInterface;

/**
 * @internal
 */
#[CoversClass(GoogleHttpClient::class)]
final class GoogleHttpClientTest extends TestCase
{
    private LoggingAwareHttpClient|MockObject $googleApiHttpClient;
    private MockObject|SerializerInterface $serializer;
    private GoogleHttpClient $googleHttpClient;

    protected function setUp(): void
    {
        $this->googleApiHttpClient = $this->createMock(LoggingAwareHttpClient::class);
        $this->serializer = $this->createMock(SerializerInterface::class);

        $googleConfiguration = new GoogleConfiguration(
            'client_id',
            'client_secret',
        );

        $this->googleHttpClient = new GoogleHttpClient($this->googleApiHttpClient, $googleConfiguration, $this->serializer);
    }

    public function testItIsInitializable(): void
    {
        self::assertInstanceOf(GoogleHttpClient::class, $this->googleHttpClient);
    }
}
