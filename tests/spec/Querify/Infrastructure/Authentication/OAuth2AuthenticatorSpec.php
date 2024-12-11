<?php

declare(strict_types=1);

namespace spec\Querify\Infrastructure\Authentication;

use PhpSpec\ObjectBehavior;
use Querify\Domain\User\Provider\UserProvider;
use Querify\Domain\UserSocialAccount\UserSocialAccountType;
use Querify\Infrastructure\Authentication\OAuth2\OAuth2Client;
use Querify\Infrastructure\Authentication\OAuth2\OAuth2ClientManager;
use Querify\Infrastructure\Authentication\OAuth2\Response\OAuth2ResourceOwner;
use Querify\Infrastructure\Authentication\OAuth2Authenticator;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Session\SessionInterface;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Exception\AuthenticationException;
use Symfony\Component\Security\Http\Authenticator\AbstractAuthenticator;
use Symfony\Component\Security\Http\Authenticator\Passport\Badge\UserBadge;
use Symfony\Component\Security\Http\Authenticator\Passport\SelfValidatingPassport;
use Symfony\Component\Security\Http\EntryPoint\AuthenticationEntryPointInterface;

class OAuth2AuthenticatorSpec extends ObjectBehavior
{
    public function let(UserProvider $userProvider, OAuth2ClientManager $oauthClientManager): void
    {
        $this->beConstructedWith($userProvider, $oauthClientManager);
    }

    public function it_is_initializable(): void
    {
        $this->shouldHaveType(OAuth2Authenticator::class);
        $this->shouldBeAnInstanceOf(AbstractAuthenticator::class);
        $this->shouldImplement(AuthenticationEntryPointInterface::class);
    }

    public function it_supports_requests_with_valid_oauth_session(Request $request, SessionInterface $session): void
    {
        $request->getSession()->willReturn($session);

        $session->get(OAuth2Authenticator::OAUTH_ACCESS_TOKEN_KEY)->willReturn('test_token');
        $session->get(OAuth2Authenticator::OAUTH_ACCESS_PROVIDER_KEY)->willReturn('SLACK');
        $session->get(OAuth2Authenticator::OAUTH_ACCESS_USER_ID)->willReturn('12345');

        $this->supports($request)->shouldReturn(true);
    }

    public function it_does_not_support_requests_without_oauth_session(Request $request, SessionInterface $session): void
    {
        $request->getSession()->willReturn($session);

        $session->get(OAuth2Authenticator::OAUTH_ACCESS_TOKEN_KEY)->willReturn(null);
        $session->get(OAuth2Authenticator::OAUTH_ACCESS_PROVIDER_KEY)->willReturn(null);
        $session->get(OAuth2Authenticator::OAUTH_ACCESS_USER_ID)->willReturn(null);

        $this->supports($request)->shouldReturn(false);
    }

    public function it_authenticates_user_with_oauth_data(
        Request $request,
        SessionInterface $session,
        UserProvider $userProvider,
        OAuth2ClientManager $oauthClientManager,
        OAuth2Client $oauthClient
    ): void {
        $request->getSession()->willReturn($session);

        $session->get(OAuth2Authenticator::OAUTH_ACCESS_TOKEN_KEY)->willReturn('test_token');
        $session->get(OAuth2Authenticator::OAUTH_ACCESS_PROVIDER_KEY)->willReturn('SLACK');
        $session->get(OAuth2Authenticator::OAUTH_ACCESS_USER_ID)->willReturn('12345');

        $oauthClientManager->getByType(UserSocialAccountType::fromString('slack'))->willReturn($oauthClient);

        $oauthClient->fetchUser('test_token', '12345')->willReturn(
            new OAuth2ResourceOwner(
                'user@local.host',
                'name',
                'externalUserId'
            )
        );

        $userProvider->loadUserByIdentifier('user@example.com')->willReturn(new \stdClass());

        $passport = $this->authenticate($request);
        $passport->shouldBeAnInstanceOf(SelfValidatingPassport::class);
        $passport->getBadge(UserBadge::class)->shouldHaveType(UserBadge::class);
    }

    public function it_handles_authentication_success(Request $request, TokenInterface $token): void
    {
        $this->onAuthenticationSuccess($request, $token, 'firewall_name')->shouldReturn(null);
    }

    public function it_handles_authentication_failure(Request $request, AuthenticationException $exception): void
    {
        $this->onAuthenticationFailure($request, $exception)
            ->shouldBeAnInstanceOf(RedirectResponse::class)
        ;
    }

    public function it_redirects_to_login_on_start(Request $request): void
    {
        $response = $this->start($request);
        $response->shouldBeAnInstanceOf(RedirectResponse::class);
        $response->getTargetUrl()->shouldReturn('/login');
    }
}
