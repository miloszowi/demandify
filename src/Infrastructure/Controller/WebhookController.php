<?php

declare(strict_types=1);

namespace Demandify\Infrastructure\Controller;

use Demandify\Infrastructure\Webhook\WebhookHandler;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

class WebhookController extends AbstractController
{
    /**
     * @param WebhookHandler[] $webhookHandlers
     */
    public function __construct(private readonly iterable $webhookHandlers) {}

    #[Route('/webhook/{type}')]
    public function webhook(Request $request, string $type): Response
    {
        foreach ($this->webhookHandlers as $webhookHandler) {
            if ($webhookHandler->supports($request, $type) && $webhookHandler->isValid($request)) {
                $webhookHandler->handle($request);

                return new Response();
            }
        }

        return new Response('', Response::HTTP_BAD_REQUEST);
    }
}
