<?php

declare(strict_types=1);

namespace Querify\Domain\UserSocialAccount;

use Querify\Domain\User\Email;
use Ramsey\Uuid\UuidInterface;

interface UserSocialAccountRepository
{
    public function findByPair(UuidInterface $userUuid, UserSocialAccountType $userSocialAccountType): ?UserSocialAccount;

    public function findByEmailAndType(Email $email, UserSocialAccountType $userSocialAccountType): ?UserSocialAccount;

    public function save(UserSocialAccount $userSocialAccount): void;
}
