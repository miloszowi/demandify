<?php

declare(strict_types=1);

namespace Querify\Infrastructure\ExternalServices\OAuth2\Response;

final readonly class OAuth2AccessResponse
{
    public function __construct(
        public string $accessToken,
        public string $externalUserId
    ) {}
}
