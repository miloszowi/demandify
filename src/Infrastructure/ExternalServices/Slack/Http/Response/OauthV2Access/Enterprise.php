<?php

declare(strict_types=1);

namespace Querify\Infrastructure\ExternalServices\Slack\Http\Response\OauthV2Access;

final readonly class Enterprise
{
    public function __construct(
        public string $name,
        public string $id
    ) {}
}
