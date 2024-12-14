<?php

declare(strict_types=1);

namespace Querify\Infrastructure\Webhook;

use Symfony\Component\HttpFoundation\Request;

interface WebhookHandler
{
    public function supports(Request $request, string $type): bool;

    public function handle(Request $request): void;

    public function isValid(Request $request): bool;
}
