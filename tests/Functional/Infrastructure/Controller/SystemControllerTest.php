<?php

declare(strict_types=1);

namespace Demandify\Tests\Functional\Infrastructure\Controller;

use Demandify\Infrastructure\Controller\SystemController;
use Demandify\Tests\Functional\BaseWebTestCase;
use PHPUnit\Framework\Attributes\CoversClass;

/**
 * @internal
 */
#[CoversClass(SystemController::class)]
final class SystemControllerTest extends BaseWebTestCase
{
    public function testHealth(): void
    {
        $client = self::createClient();
        $client->request('GET', '/_system/health');

        self::assertResponseIsSuccessful();

        $expected = json_encode([
            'database' => true,
            'redis' => true,
            'rabbitmq' => true,
        ]);

        self::assertSame($expected, $client->getResponse()->getContent());
    }

    public function testMetrics(): void
    {
        $client = self::createClient();
        $client->request('GET', '/_system/metrics');

        self::assertResponseIsSuccessful();

        // TODO: To implement when metrics are available
    }
}
