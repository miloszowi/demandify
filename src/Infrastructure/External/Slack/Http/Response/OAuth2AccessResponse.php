<?php

declare(strict_types=1);

namespace Demandify\Infrastructure\External\Slack\Http\Response;

use Demandify\Infrastructure\External\Slack\Http\Response\OAuth2Access\AuthedUser;
use Demandify\Infrastructure\External\Slack\Http\Response\OAuth2Access\Enterprise;
use Demandify\Infrastructure\External\Slack\Http\Response\OAuth2Access\Team;
use Symfony\Component\Serializer\Attribute\SerializedName;

final readonly class OAuth2AccessResponse
{
    public function __construct(
        public bool $ok,
        public ?string $error = null,
        #[SerializedName('access_token')]
        public ?string $accessToken = null,
        #[SerializedName('token_type')]
        public ?string $tokenType = null,
        public ?string $scope = null,
        #[SerializedName('bot_user_id')]
        public ?string $botUserId = null,
        #[SerializedName('app_id')]
        public ?string $appId = null,
        public ?Team $team = null,
        public ?Enterprise $enterprise = null,
        #[SerializedName('authed_user')]
        public ?AuthedUser $authedUser = null
    ) {}
}
