<?php

declare(strict_types=1);

namespace spec\Querify\Infrastructure\Authentication\OAuth2;

use PhpSpec\ObjectBehavior;
use Querify\Domain\UserSocialAccount\UserSocialAccountType;
use Querify\Infrastructure\Authentication\OAuth2\OAuth2Client;
use Querify\Infrastructure\Authentication\OAuth2\OAuth2ClientManager;
use Querify\Infrastructure\Authentication\OAuth2\Request\OAuth2AccessRequest;
use Querify\Infrastructure\Authentication\OAuth2\Response\OAuth2AccessResponse;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Session\SessionInterface;

class OAuth2ClientManagerSpec extends ObjectBehavior
{
    public function let(OAuth2Client $oauthClient): void
    {
        $this->beConstructedWith([$oauthClient]);
    }

    public function it_is_initializable(): void
    {
        $this->shouldHaveType(OAuth2ClientManager::class);
    }

    public function it_should_get_oauth_client_by_request(
        OAuth2Client $oauthClient
    ): void {
        $oauthRequest = new OAuth2AccessRequest(
            'session-id',
            'state',
            'code',
            new Request()
        );

        $oauthClient->supports('session-id', 'state')->willReturn(true);
        $this->get($oauthRequest)->shouldReturn($oauthClient);
    }

    public function it_should_throw_exception_if_no_oauth_client_supports_request(OAuth2Client $oauthClient): void
    {
        $oauthRequest = new OAuth2AccessRequest(
            'session-id',
            'state',
            'code',
            new Request()
        );
        $oauthClient->supports('session-id', 'state')->willReturn(false);
        $this->beConstructedWith([$oauthClient]);

        $this->shouldThrow(\LogicException::class)
            ->during('get', [$oauthRequest])
        ;
    }

    public function it_should_get_oauth_client_by_type(
        OAuth2Client $oauthClient
    ): void {
        $type = UserSocialAccountType::SLACK;
        $oauthClient->getLinkedSocialAccountType()->willReturn($type);

        $this->getByType($type)->shouldReturn($oauthClient);
    }

    public function it_should_throw_exception_if_no_oauth_client_found_by_type(
        OAuth2Client $oauthClient
    ): void {
        $oauthClient->getLinkedSocialAccountType()->willReturn(UserSocialAccountType::SLACK);
        $this->beConstructedWith([$oauthClient]);

        $this->shouldThrow(\LogicException::class)
            ->during('getByType', [UserSocialAccountType::GOOGLE])
        ;
    }

    public function it_should_authorize_oauth_client(
        OAuth2Client $oauthClient,
        Request $request,
        SessionInterface $session
    ): void {
        $response = new OAuth2AccessResponse(
            'access-token',
            'external-user-id',
        );
        $oauthClient->getLinkedSocialAccountType()->willReturn(UserSocialAccountType::SLACK);
        $oauthClient->supports('session-id', 'state')->willReturn(true);
        $oauthClient->authorize('code')->willReturn($response);

        $this->beConstructedWith([$oauthClient]);

        $request->getSession()->willReturn($session);
        $request = new OAuth2AccessRequest(
            'session-id',
            'state',
            'code',
            $request->getWrappedObject()
        );
        $this->authorize($request)->shouldReturn($response);
    }

    public function it_should_throw_exception_if_no_oauth_client_supports_authorize(
        OAuth2Client $oauthClient
    ): void {
        $oauthClient->supports('session-id', 'state')->willReturn(false);

        $this->beConstructedWith([$oauthClient]);

        $request = new OAuth2AccessRequest(
            'session-id',
            'state',
            'code',
            new Request()
        );
        $this->shouldThrow(\LogicException::class)
            ->during('authorize', [$request])
        ;
    }

    public function it_should_start_oauth_flow(
        OAuth2Client $oauthClient,
        Request $request,
    ): void {
        $type = UserSocialAccountType::SLACK;
        $oauthClient->getLinkedSocialAccountType()->willReturn($type);
        $oauthClient->start($request)->willReturn(new RedirectResponse('http://example.com'));
        $this->beConstructedWith([$oauthClient]);

        $this->start($request, $type)->shouldReturnAnInstanceOf(RedirectResponse::class);
    }

    public function it_should_throw_exception_if_no_oauth_client_supports_start(
        Request $request,
        OAuth2Client $oauthClient,
    ): void {
        $type = UserSocialAccountType::SLACK;
        $oauthClient->getLinkedSocialAccountType()->willReturn($type);

        $this->beConstructedWith([$oauthClient]);

        $this->shouldThrow(\LogicException::class)
            ->during('start', [$request, UserSocialAccountType::GOOGLE])
        ;
    }
}
