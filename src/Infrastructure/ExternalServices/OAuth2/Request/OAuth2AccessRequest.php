<?php

declare(strict_types=1);

namespace Querify\Infrastructure\ExternalServices\OAuth2\Request;

use Symfony\Component\HttpFoundation\Request;

final readonly class OAuth2AccessRequest
{
    public function __construct(
        public string $sessionId,
        public string $state,
        public string $code,
        public Request $originalRequest,
    ) {}
}
