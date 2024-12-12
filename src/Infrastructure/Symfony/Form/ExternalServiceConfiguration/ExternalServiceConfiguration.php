<?php

declare(strict_types=1);

namespace Querify\Infrastructure\Symfony\Form\ExternalServiceConfiguration;

use Querify\Domain\User\User;

class ExternalServiceConfiguration
{
    public function __construct(
        public string $externalServiceName,
        /**
         * @var User[]
         */
        public array $eligibleApprovers = []
    ) {}
}
