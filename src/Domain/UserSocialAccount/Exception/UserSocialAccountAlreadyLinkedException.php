<?php

declare(strict_types=1);

namespace Querify\Domain\UserSocialAccount\Exception;

use Querify\Domain\UserSocialAccount\UserSocialAccountType;
use Ramsey\Uuid\UuidInterface;

class UserSocialAccountAlreadyLinkedException extends \Exception
{
    public static function fromPair(UuidInterface $userUuid, UserSocialAccountType $userSocialAccountType): self
    {
        return new self(
            \sprintf(
                'User with id of "%s" has already linked social account of type "%s"',
                $userUuid->toString(),
                $userSocialAccountType->value
            )
        );
    }
}
