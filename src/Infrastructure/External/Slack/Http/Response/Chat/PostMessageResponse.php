<?php

declare(strict_types=1);

namespace Demandify\Infrastructure\External\Slack\Http\Response\Chat;

use Symfony\Component\Serializer\Attribute\SerializedName;

readonly class PostMessageResponse
{
    public function __construct(
        public bool $ok,
        public ?string $error = null,
        public ?string $channel = null,
        #[SerializedName('ts')]
        public ?string $timestamp = null,
    ) {}
}
