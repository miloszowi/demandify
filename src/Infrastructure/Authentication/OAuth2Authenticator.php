<?php

declare(strict_types=1);

namespace Demandify\Infrastructure\Authentication;

use Demandify\Domain\User\Provider\UserProvider;
use Demandify\Infrastructure\Authentication\OAuth2\Provider\OAuth2ClientResolver;
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
    public function __construct(
        private readonly OAuth2ClientResolver $clientResolver,
        private readonly UserProvider $userProvider,
    ) {}

    public function supports(Request $request): ?bool
    {
        return !empty($request->getSession()->get(AccessToken::class));
    }

    public function authenticate(Request $request): Passport
    {
        /** @var AccessToken $accessToken */
        $accessToken = $request->getSession()->get(AccessToken::class);
        $client = $this->clientResolver->byType($accessToken->type);

        if (false === $client->checkAuth($accessToken->value)) {
            throw new AuthenticationException('Failed to authenticate.');
        }

        return new SelfValidatingPassport(
            new UserBadge($accessToken->email, $this->userProvider->loadUserByIdentifier(...))
        );
    }

    public function onAuthenticationSuccess(Request $request, TokenInterface $token, string $firewallName): ?Response
    {
        return null;
    }

    public function onAuthenticationFailure(Request $request, AuthenticationException $exception): ?Response
    {
        $request->getSession()->remove(AccessToken::class);

        return new RedirectResponse('/login');
    }

    public function start(Request $request, ?AuthenticationException $authException = null): Response
    {
        return new RedirectResponse('/login');
    }
}
