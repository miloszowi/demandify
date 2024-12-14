<?php

declare(strict_types=1);

namespace Querify\Domain\UserSocialAccount\Exception;

use Querify\Domain\UserSocialAccount\UserSocialAccountType;

class UserSocialAccountNotFound extends \Exception
{
    public static function fromExternalIdAndType(string $externalId, UserSocialAccountType $userSocialAccountType): self
    {
        return new self(
            \sprintf(
                'User with external id of "%s" of type "%s" was not found.',
                $externalId,
                $userSocialAccountType->value
            )
        );
    }
}
