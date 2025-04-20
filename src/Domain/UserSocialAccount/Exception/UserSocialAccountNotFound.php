<?php

declare(strict_types=1);

namespace Demandify\Domain\UserSocialAccount\Exception;

use Demandify\Domain\UserSocialAccount\UserSocialAccountType;
use Ramsey\Uuid\UuidInterface;

class UserSocialAccountNotFound extends \Exception
{
    public static function fromExternalIdAndType(string $externalId, UserSocialAccountType $userSocialAccountType): self
    {
        return new self(
            \sprintf(
                'User social account with external id of "%s" of type "%s" was not found.',
                $externalId,
                $userSocialAccountType->value
            )
        );
    }

    public static function fromUserUuidIdAndType(UuidInterface $userUuid, UserSocialAccountType $userSocialAccountType): self
    {
        return new self(
            \sprintf(
                'User social account for user uuid of "%s" and type "%s" was not found.',
                $userUuid->toString(),
                $userSocialAccountType->value
            )
        );
    }
}
