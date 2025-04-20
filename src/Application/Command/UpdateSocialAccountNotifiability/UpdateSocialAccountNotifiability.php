<?php

declare(strict_types=1);

namespace Demandify\Application\Command\UpdateSocialAccountNotifiability;

use Demandify\Application\Command\Command;
use Demandify\Domain\UserSocialAccount\UserSocialAccountType;
use Ramsey\Uuid\UuidInterface;

readonly class UpdateSocialAccountNotifiability implements Command
{
    public function __construct(
        public UuidInterface $userUuid,
        public UserSocialAccountType $socialAccountType,
        public bool $notifiable,
    ) {}
}
