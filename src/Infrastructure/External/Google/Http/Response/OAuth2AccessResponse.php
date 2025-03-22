<?php

declare(strict_types=1);

namespace Demandify\Infrastructure\External\Google\Http\Response;

use Symfony\Component\Serializer\Attribute\SerializedName;

readonly class OAuth2AccessResponse
{
    public function __construct(
        #[SerializedName('access_token')]
        public string $accessToken,
        #[SerializedName('expires_in')]
        public int $expiresIn,
        #[SerializedName('id_token')]
        public string $tokenId,
        #[SerializedName('scope')]
        public string $scope,
        #[SerializedName('token_type')]
        public string $tokenType,
    ) {}
}
