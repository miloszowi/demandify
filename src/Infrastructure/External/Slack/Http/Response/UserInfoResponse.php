<?php

declare(strict_types=1);

namespace Demandify\Infrastructure\External\Slack\Http\Response;

use Demandify\Infrastructure\External\Slack\Http\Response\UserInfo\User;

final readonly class UserInfoResponse
{
    public function __construct(
        public bool $ok,
        public ?string $error,
        public ?User $user
    ) {}
}
