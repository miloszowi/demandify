<?php

declare(strict_types=1);

namespace Demandify\Domain\UserSocialAccount;

use Demandify\Domain\User\Email;
use Ramsey\Uuid\UuidInterface;

interface UserSocialAccountRepository
{
    public function findByUserUuidAndType(UuidInterface $userUuid, UserSocialAccountType $userSocialAccountType): ?UserSocialAccount;

    public function getByUserUuidAndType(UuidInterface $userUuid, UserSocialAccountType $userSocialAccountType): UserSocialAccount;

    public function getByExternalIdAndType(string $externalId, UserSocialAccountType $userSocialAccountType): ?UserSocialAccount;

    public function findByEmailAndType(Email $email, UserSocialAccountType $userSocialAccountType): ?UserSocialAccount;

    public function save(UserSocialAccount $userSocialAccount): void;
}
