<?php

declare(strict_types=1);

namespace Querify\Infrastructure\ExternalServices\OAuth2;

use Querify\Domain\UserSocialAccount\UserSocialAccountType;
use Querify\Infrastructure\Authentication\OAuth2Authenticator;
use Querify\Infrastructure\ExternalServices\OAuth2\Request\OAuth2AccessRequest;
use Querify\Infrastructure\ExternalServices\OAuth2\Response\OAuth2AccessResponse;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;

class OAuth2ClientManager
{
    public function __construct(
        /** @var OAuth2Client[] $oauthClients */
        private readonly iterable $oauthClients
    ) {}

    public function get(OAuth2AccessRequest $request): OAuth2Client
    {
        foreach ($this->oauthClients as $oauthClient) {
            if ($oauthClient->supports($request->sessionId, $request->state)) {
                return $oauthClient;
            }
        }

        throw new \LogicException('No supported OAuth2 client found.');
    }

    public function getByType(UserSocialAccountType $type): OAuth2Client
    {
        foreach ($this->oauthClients as $oauthClient) {
            if ($oauthClient->getLinkedSocialAccountType()->isEqualTo($type)) {
                return $oauthClient;
            }
        }

        throw new \LogicException('No supported OAuth2 client found.');
    }

    public function authorize(OAuth2AccessRequest $request): OAuth2AccessResponse
    {
        foreach ($this->oauthClients as $oauthClient) {
            if ($oauthClient->supports($request->sessionId, $request->state)) {
                $authorizationResponse = $oauthClient->authorize($request->code);

                $request->originalRequest->getSession()->set(
                    OAuth2Authenticator::OAUTH_ACCESS_TOKEN_KEY,
                    $authorizationResponse->accessToken
                );
                $request->originalRequest->getSession()->set(
                    OAuth2Authenticator::OAUTH_ACCESS_PROVIDER_KEY,
                    $oauthClient->getLinkedSocialAccountType()->value
                );
                $request->originalRequest->getSession()->set(
                    OAuth2Authenticator::OAUTH_ACCESS_USER_ID,
                    $authorizationResponse->externalUserId
                );

                return $authorizationResponse;
            }
        }

        throw new \LogicException('No supported OAuth2 client found.');
    }

    public function start(Request $request, UserSocialAccountType $type): RedirectResponse
    {
        foreach ($this->oauthClients as $oauthClient) {
            if ($oauthClient->getLinkedSocialAccountType()->isEqualTo($type)) {
                return $oauthClient->start($request);
            }
        }

        throw new \LogicException('No supported OAuth2 client found.');
    }
}
