<?php

declare(strict_types=1);

namespace Demandify\Tests\Unit\Infrastructure\External\Google\Http;

use Demandify\Infrastructure\External\Google\GoogleConfiguration;
use Demandify\Infrastructure\External\Google\Http\GoogleHttpClient;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Serializer\SerializerInterface;
use Symfony\Contracts\HttpClient\HttpClientInterface;

/**
 * @internal
 */
#[CoversClass(GoogleHttpClient::class)]
final class GoogleHttpClientTest extends TestCase
{
    private HttpClientInterface|MockObject $googleApiHttpClient;
    private MockObject|SerializerInterface $serializer;
    private GoogleHttpClient $googleHttpClient;

    protected function setUp(): void
    {
        $this->googleApiHttpClient = $this->createMock(HttpClientInterface::class);
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
