<?php

declare(strict_types=1);

namespace Querify\Application\Command\NotifyEligibleApprover;

use Ramsey\Uuid\UuidInterface;
use Symfony\Component\Messenger\Attribute\AsMessage;

#[AsMessage]
final readonly class NotifyEligibleApprover
{
    public function __construct(
        public UuidInterface $eligibleApproverUuid,
        public UuidInterface $demandUuid,
    ) {}
}
