<?php

declare(strict_types=1);

namespace Querify\Infrastructure\External\Slack\Http\Response\UserInfo;

use Symfony\Component\Serializer\Attribute\SerializedName;

final readonly class Profile
{
    public function __construct(
        public ?string $title,
        public ?string $phone,
        public ?string $skype,
        #[SerializedName('real_name')]
        public ?string $realName,
        #[SerializedName('real_name_normalized')]
        public ?string $realNameNormalized,
        #[SerializedName('display_name')]
        public ?string $displayName,
        #[SerializedName('display_name_normalized')]
        public ?string $displayNameNormalized,
        /** @var ?string[] $fields */
        public ?array $fields,
        #[SerializedName('status_text')]
        public ?string $statusText,
        #[SerializedName('status_emoji')]
        public ?string $statusEmoji,
        /** @var ?string[] $statusEmojiDisplayInfo */
        #[SerializedName('status_emoji_display_info')]
        public ?array $statusEmojiDisplayInfo,
        #[SerializedName('status_expiration')]
        public int $statusExpiration,
        #[SerializedName('avatar_hash')]
        public ?string $avatarHash,
        #[SerializedName('image_original')]
        public ?string $imageOriginal,
        #[SerializedName('is_custom_image')]
        public ?bool $isCustomImage,
        public ?string $email,
        #[SerializedName('first_name')]
        public ?string $firstName,
        #[SerializedName('last_name')]
        public ?string $lastName,
        #[SerializedName('image_24')]
        public ?string $image24,
        #[SerializedName('image_32')]
        public ?string $image32,
        #[SerializedName('image_48')]
        public ?string $image48,
        #[SerializedName('image_72')]
        public ?string $image72,
        #[SerializedName('image_192')]
        public ?string $image192,
        #[SerializedName('image_512')]
        public ?string $image512,
        #[SerializedName('image_1024')]
        public ?string $image1024,
        #[SerializedName('status_text_canonical')]
        public ?string $statusTextCanonical,
        public ?string $team
    ) {}
}
