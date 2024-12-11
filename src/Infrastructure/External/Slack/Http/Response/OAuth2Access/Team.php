<?php

declare(strict_types=1);

namespace Querify\Infrastructure\External\Slack\Http\Response\OAuth2Access;

final readonly class Team
{
    public function __construct(
        public string $name,
        public string $id
    ) {}
}
