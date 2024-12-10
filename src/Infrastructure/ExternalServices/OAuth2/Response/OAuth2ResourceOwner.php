<?php

declare(strict_types=1);

namespace Querify\Infrastructure\ExternalServices\OAuth2\Response;

final readonly class OAuth2ResourceOwner
{
    public function __construct(
        public string $email,
        public string $name,
        public string $externalUserId,
        /** @var array<mixed> */
        public array $extraData = []
    ) {}
}
