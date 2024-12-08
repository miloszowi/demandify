<?php

declare(strict_types=1);

namespace Querify\Infrastructure\Twig;

class OAuthFrontFriendlyHandler
{
    public function __construct(
        public readonly string $name,
        public readonly string $url,
        public readonly bool $isConnected
    ) {}
}
