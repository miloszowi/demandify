<?php

declare(strict_types=1);

namespace Demandify\Infrastructure\External\Slack\Http\Response\OAuth2Access;

final readonly class Enterprise
{
    public function __construct(
        public string $name,
        public string $id
    ) {}
}
