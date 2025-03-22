<?php

declare(strict_types=1);

namespace Demandify\Infrastructure\External\Google\Http\Response;

readonly class UserInfoResponse
{
    public function __construct(
        public string $id,
        public string $email,
        public string $name,
        public string $picture,
    ) {}
}
