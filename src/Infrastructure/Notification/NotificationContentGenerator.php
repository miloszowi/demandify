<?php

declare(strict_types=1);

namespace Querify\Infrastructure\Notification;

use Querify\Domain\UserSocialAccount\UserSocialAccountType;

interface NotificationContentGenerator
{
    public const string NEW_DEMAND_TEMPLATE = 'new_demand';

    /**
     * @param string[] $data
     */
    public function generate(string $template, array $data): string;

    /**
     * @return mixed[]
     */
    public function generateAttachments(string $template, string $demandIdentifier): array;

    public function supports(UserSocialAccountType $userSocialAccountType): bool;
}
