<?php

declare(strict_types=1);

namespace Demandify\Domain\UserSocialAccount;

enum UserSocialAccountType: string
{
    case SLACK = 'SLACK';
    case GOOGLE = 'GOOGLE';

    public function isEqualTo(UserSocialAccountType $type): bool
    {
        return $type->value === $this->value;
    }

    public static function fromString(string $value): self
    {
        return self::from(
            strtoupper($value)
        );
    }
}
