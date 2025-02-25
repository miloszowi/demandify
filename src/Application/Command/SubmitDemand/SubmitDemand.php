<?php

declare(strict_types=1);

namespace Demandify\Application\Command\SubmitDemand;

use Symfony\Component\Messenger\Attribute\AsMessage;

#[AsMessage]
final readonly class SubmitDemand
{
    public function __construct(
        public string $requesterEmail,
        public string $service,
        public string $content,
        public string $reason,
    ) {}
}
