<?php

declare(strict_types=1);

namespace Demandify\Infrastructure\Symfony\Form\ExternalServiceConfiguration;

use Demandify\Domain\User\User;

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
