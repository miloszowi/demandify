<?php

declare(strict_types=1);

namespace Querify\Infrastructure\OAuth\Slack\Http\Response;

use Querify\Infrastructure\OAuth\Slack\Http\Response\OauthV2Access\AuthedUser;
use Querify\Infrastructure\OAuth\Slack\Http\Response\OauthV2Access\Enterprise;
use Querify\Infrastructure\OAuth\Slack\Http\Response\OauthV2Access\Team;
use Symfony\Component\Serializer\Attribute\SerializedName;

final readonly class OauthV2AccessResponse
{
    public function __construct(
        public bool $ok,
        public ?string $error,
        #[SerializedName('access_token')]
        public ?string $accessToken,
        #[SerializedName('token_type')]
        public ?string $tokenType,
        public ?string $scope,
        #[SerializedName('bot_user_id')]
        public ?string $botUserId,
        #[SerializedName('app_id')]
        public ?string $appId,
        public ?Team $team,
        public ?Enterprise $enterprise,
        #[SerializedName('authed_user')]
        public ?AuthedUser $authedUser
    ) {}
}
