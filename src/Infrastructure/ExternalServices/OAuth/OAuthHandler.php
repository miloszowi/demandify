<?php

declare(strict_types=1);

namespace Querify\Infrastructure\ExternalServices\OAuth;

interface OAuthHandler
{
    public function supports(string $state, string $userIdentifier): bool;

    public function handle(string $code, string $userIdentifier): void;

    public function provideState(string $userIdentifier): string;

    public function getOauthUrl(): string;

    public function getName(): string;
}
