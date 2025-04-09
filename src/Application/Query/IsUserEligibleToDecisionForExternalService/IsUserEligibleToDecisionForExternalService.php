<?php

declare(strict_types=1);

namespace Demandify\Application\Query\IsUserEligibleToDecisionForExternalService;

use Demandify\Application\Query\Query;
use Demandify\Domain\User\User;

readonly class IsUserEligibleToDecisionForExternalService implements Query
{
    public function __construct(
        public User $user,
        public string $externalServiceName
    ) {}
}