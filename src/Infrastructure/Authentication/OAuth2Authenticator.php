<?php

declare(strict_types=1);

namespace Demandify\Infrastructure\Authentication;

use Demandify\Domain\User\Provider\UserProvider;
use Demandify\Domain\UserSocialAccount\UserSocialAccountType;
use Demandify\Infrastructure\Authentication\OAuth2\OAuth2ClientManager;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Exception\AuthenticationException;
use Symfony\Component\Security\Http\Authenticator\AbstractAuthenticator;
use Symfony\Component\Security\Http\Authenticator\AuthenticatorInterface;
use Symfony\Component\Security\Http\Authenticator\Passport\Badge\UserBadge;
use Symfony\Component\Security\Http\Authenticator\Passport\Passport;
use Symfony\Component\Security\Http\Authenticator\Passport\SelfValidatingPassport;
use Symfony\Component\Security\Http\EntryPoint\AuthenticationEntryPointInterface;

class OAuth2Authenticator extends AbstractAuthenticator implements AuthenticatorInterface, AuthenticationEntryPointInterface
{
    public const string OAUTH_ACCESS_TOKEN_KEY = 'oauth_access_token';
    public const string OAUTH_ACCESS_PROVIDER_KEY = 'oauth_provider';
    public const string OAUTH_ACCESS_USER_ID = 'oauth_user_id';

    public function __construct(
        private readonly UserProvider $userProvider,
        private readonly OAuth2ClientManager $oauthClientManager,
    ) {}

    public function supports(Request $request): ?bool
    {
        return !empty($request->getSession()->get(self::OAUTH_ACCESS_TOKEN_KEY))
            && !empty($request->getSession()->get(self::OAUTH_ACCESS_PROVIDER_KEY))
            && !empty($request->getSession()->get(self::OAUTH_ACCESS_USER_ID));
    }

    public function authenticate(Request $request): Passport
    {
        $oauthClient = $this->oauthClientManager->getByType(
            UserSocialAccountType::fromString(
                $request->getSession()->get(self::OAUTH_ACCESS_PROVIDER_KEY)
            )
        );

        $oauthUser = $oauthClient->fetchUser(
            $request->getSession()->get(self::OAUTH_ACCESS_TOKEN_KEY),
            $request->getSession()->get(self::OAUTH_ACCESS_USER_ID)
        );

        return new SelfValidatingPassport(
            new UserBadge($oauthUser->email, $this->userProvider->loadUserByIdentifier(...))
        );
    }

    public function onAuthenticationSuccess(Request $request, TokenInterface $token, string $firewallName): ?Response
    {
        return null;
    }

    public function onAuthenticationFailure(Request $request, AuthenticationException $exception): ?Response
    {
        return new RedirectResponse('/login');
    }

    public function start(Request $request, ?AuthenticationException $authException = null): Response
    {
        return new RedirectResponse('/login');
    }
}
