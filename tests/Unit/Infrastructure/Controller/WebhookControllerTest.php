<?php

declare(strict_types=1);

namespace Demandify\Tests\Unit\Infrastructure\Controller;

use Demandify\Infrastructure\Controller\WebhookController;
use Demandify\Infrastructure\Webhook\WebhookHandler;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * @internal
 */
#[CoversClass(WebhookController::class)]
final class WebhookControllerTest extends TestCase
{
    public function testHandlesWebhook(): void
    {
        $request = $this->createMock(Request::class);
        $webhookHandlerMock = $this->createMock(WebhookHandler::class);
        $webhookHandlerMock
            ->expects(self::once())
            ->method('supports')
            ->with($request, 'slack')
            ->willReturn(true)
        ;
        $webhookHandlerMock
            ->expects(self::once())
            ->method('isValid')
            ->with($request)
            ->willReturn(true)
        ;
        $controller = new WebhookController([$webhookHandlerMock]);

        $response = $controller->webhook($request, 'slack');

        self::assertSame(Response::HTTP_OK, $response->getStatusCode());
    }

    public function testReturnsBadRequestForUnsupportedWebhook(): void
    {
        $request = $this->createMock(Request::class);
        $webhookHandlerMock = $this->createMock(WebhookHandler::class);
        $webhookHandlerMock
            ->expects(self::once())
            ->method('supports')
            ->with($request, 'unsupported')
            ->willReturn(false)
        ;
        $controller = new WebhookController([$webhookHandlerMock]);

        $response = $controller->webhook($request, 'unsupported');

        self::assertSame(Response::HTTP_BAD_REQUEST, $response->getStatusCode());
    }
}
