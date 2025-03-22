<?php

declare(strict_types=1);

namespace Demandify\Infrastructure\External\Google;

use Symfony\Component\DependencyInjection\Attribute\Autowire;

class GoogleConfiguration
{
    public function __construct(
        #[Autowire(env: 'GOOGLE_CLIENT_ID')]
        public string $clientId,
        #[
            \SensitiveParameter,
            Autowire(env: 'GOOGLE_CLIENT_SECRET')
        ]
        public string $clientSecret,
    ) {}
}
