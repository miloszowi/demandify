<?php

declare(strict_types=1);

namespace Querify\Infrastructure\ExternalServices\Slack\Http\Response\UserInfo;

use Symfony\Component\Serializer\Attribute\SerializedName;

final readonly class User
{
    public function __construct(
        public string $id,
        #[SerializedName('team_id')]
        public string $teamId,
        public string $name,
        public bool $deleted,
        public string $color,
        #[SerializedName('real_name')]
        public string $realName,
        public string $tz,
        #[SerializedName('tz_label')]
        public string $tzLabel,
        #[SerializedName('tz_offset')]
        public int $tzOffset,
        public Profile $profile,
        #[SerializedName('is_admin')]
        public bool $isAdmin,
        #[SerializedName('is_owner')]
        public bool $isOwner,
        #[SerializedName('is_primary_owner')]
        public bool $isPrimaryOwner,
        #[SerializedName('is_restricted')]
        public bool $isRestricted,
        #[SerializedName('is_ultra_restricted')]
        public bool $isUltraRestricted,
        #[SerializedName('is_bot')]
        public bool $isBot,
        #[SerializedName('is_app_user')]
        public bool $isAppUser,
        public int $updated,
        #[SerializedName('is_email_confirmed')]
        public bool $isEmailConfirmed,
        #[SerializedName('has_2fa')]
        public bool $has2FA,
        #[SerializedName('who_can_share_contact_card')]
        public string $whoCanShareContactCard,
    ) {}
}
