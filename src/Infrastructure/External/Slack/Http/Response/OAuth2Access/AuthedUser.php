<?php

declare(strict_types=1);

namespace Demandify\Infrastructure\External\Slack\Http\Response\OAuth2Access;

use Symfony\Component\Serializer\Attribute\SerializedName;

final readonly class AuthedUser
{
    public function __construct(
        public string $id,
        public string $scope,
        #[SerializedName('access_token')]
        public string $accessToken,
        #[SerializedName('token_type')]
        public string $tokenType
    ) {}
}
