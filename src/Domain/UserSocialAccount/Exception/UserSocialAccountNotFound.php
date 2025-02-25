<?php

declare(strict_types=1);

namespace Demandify\Domain\UserSocialAccount\Exception;

use Demandify\Domain\UserSocialAccount\UserSocialAccountType;

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
