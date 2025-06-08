<?php

declare(strict_types=1);

namespace Demandify\Tests\Unit\Infrastructure\Authentication;

use Demandify\Domain\User\Provider\UserProvider;
use Demandify\Domain\UserSocialAccount\UserSocialAccountType;
use Demandify\Infrastructure\Authentication\AccessToken;
use Demandify\Infrastructure\Authentication\OAuth2\Provider\OAuth2Client;
use Demandify\Infrastructure\Authentication\OAuth2\Provider\OAuth2ClientResolver;
use Demandify\Infrastructure\Authentication\OAuth2Authenticator;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Session\SessionInterface;
use Symfony\Component\Security\Core\Authentication\Token\NullToken;
use Symfony\Component\Security\Core\Exception\AuthenticationException;
use Symfony\Component\Security\Http\Authenticator\AbstractAuthenticator;
use Symfony\Component\Security\Http\Authenticator\Passport\Badge\UserBadge;
use Symfony\Component\Security\Http\Authenticator\Passport\SelfValidatingPassport;

/**
 * @internal
 */
#[CoversClass(OAuth2Authenticator::class)]
final class OAuth2AuthenticatorTest extends TestCase
{
    private UserProvider $userProviderMock;
    private MockObject|OAuth2ClientResolver $clientResolver;
    private OAuth2Authenticator $oAuth2Authenticator;

    protected function setUp(): void
    {
        $this->userProviderMock = $this->createMock(UserProvider::class);
        $this->clientResolver = $this->createMock(OAuth2ClientResolver::class);
        $this->oAuth2Authenticator = new OAuth2Authenticator($this->clientResolver, $this->userProviderMock);
    }

    public function testInitializable(): void
    {
        self::assertInstanceOf(OAuth2Authenticator::class, $this->oAuth2Authenticator);
        self::assertInstanceOf(AbstractAuthenticator::class, $this->oAuth2Authenticator);
    }

    public function testSupportsRequestsWithAccessTokenSetInSession(): void
    {
        $requestMock = $this->createMock(Request::class);
        $sessionMock = $this->createMock(SessionInterface::class);
        $accessTokenMock = $this->createMock(AccessToken::class);

        $requestMock->expects(self::once())->method('getSession')->willReturn($sessionMock);
        $sessionMock->expects(self::once())->method('get')->with(AccessToken::class)->willReturn($accessTokenMock);
        self::assertTrue($this->oAuth2Authenticator->supports($requestMock));

        $requestMock = $this->createMock(Request::class);
        $sessionMock = $this->createMock(SessionInterface::class);

        $requestMock->expects(self::once())->method('getSession')->willReturn($sessionMock);
        $sessionMock->expects(self::once())->method('get')->with(AccessToken::class)->willReturn(null);
        self::assertFalse($this->oAuth2Authenticator->supports($requestMock));
    }

    public function testAuthenticatesUserWithAccessToken(): void
    {
        $requestMock = $this->createMock(Request::class);
        $sessionMock = $this->createMock(SessionInterface::class);
        $accessToken = new AccessToken(
            UserSocialAccountType::SLACK,
            'email@local.host',
            'token',
            3600
        );

        $clientMock = $this->createMock(OAuth2Client::class);
        $clientMock->method('checkAuth')->with($accessToken->value)->willReturn(true);

        $requestMock->expects(self::once())->method('getSession')->willReturn($sessionMock);
        $sessionMock->expects(self::once())->method('get')->with(AccessToken::class)->willReturn($accessToken);
        $this->clientResolver->expects(self::once())->method('byType')->with($accessToken->type)->willReturn($clientMock);
        $passport = $this->oAuth2Authenticator->authenticate($requestMock);

        self::assertInstanceOf(SelfValidatingPassport::class, $passport);
        self::assertInstanceOf(UserBadge::class, $passport->getBadge(UserBadge::class));
    }

    public function testExceptionThrownWhenAuthenticationFails(): void
    {
        $requestMock = $this->createMock(Request::class);
        $sessionMock = $this->createMock(SessionInterface::class);
        $accessToken = new AccessToken(
            UserSocialAccountType::SLACK,
            'email@local.host',
            'token',
            3600
        );

        $clientMock = $this->createMock(OAuth2Client::class);
        $clientMock->method('checkAuth')->with($accessToken->value)->willReturn(false);

        $requestMock->expects(self::once())->method('getSession')->willReturn($sessionMock);
        $sessionMock->expects(self::once())->method('get')->with(AccessToken::class)->willReturn($accessToken);
        $this->clientResolver->expects(self::once())->method('byType')->with($accessToken->type)->willReturn($clientMock);
        $this->expectException(AuthenticationException::class);
        $this->expectExceptionMessage('Failed to authenticate.');
        $this->oAuth2Authenticator->authenticate($requestMock);
    }

    public function testOnAuthenticationSuccess(): void
    {
        self::expectNotToPerformAssertions();
        $this->oAuth2Authenticator->onAuthenticationSuccess(
            $this->createMock(Request::class),
            new NullToken(),
            'main'
        );
    }

    public function testOnAuthenticationFailure(): void
    {
        $request = $this->createMock(Request::class);
        $session = $this->createMock(SessionInterface::class);
        $request
            ->expects(self::once())
            ->method('getSession')
            ->willReturn($session)
        ;

        $session
            ->expects(self::once())
            ->method('remove')
            ->with(AccessToken::class)
        ;
        $response = $this->oAuth2Authenticator->onAuthenticationFailure($request, $this->createMock(AuthenticationException::class));

        self::assertInstanceOf(RedirectResponse::class, $response);
        self::assertSame('/login', $response->getTargetUrl());
    }

    public function testStart(): void
    {
        $response = $this->oAuth2Authenticator->start($this->createMock(Request::class));

        self::assertInstanceOf(RedirectResponse::class, $response);
        self::assertSame('/login', $response->getTargetUrl());
    }
}
