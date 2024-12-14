<?php

declare(strict_types=1);

namespace Querify\Infrastructure\Notification\Client\Exception;

use Querify\Domain\UserSocialAccount\UserSocialAccountType;

class NotificationClientNotImplementedException extends \Exception
{
    public static function fromUserSocialAccountType(UserSocialAccountType $userSocialAccountType): self
    {
        return new self(
            \sprintf('Notification client for type "%s" is not implemented', $userSocialAccountType->value)
        );
    }
}
