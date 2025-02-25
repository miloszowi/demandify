<?php

declare(strict_types=1);

namespace Tests\Demandify\Infrastructure\Authentication;

use Demandify\Domain\User\Provider\UserProvider;
use Demandify\Domain\UserSocialAccount\UserSocialAccountType;
use Demandify\Infrastructure\Authentication\OAuth2\OAuth2Client;
use Demandify\Infrastructure\Authentication\OAuth2\OAuth2ClientManager;
use Demandify\Infrastructure\Authentication\OAuth2\Response\OAuth2ResourceOwner;
use Demandify\Infrastructure\Authentication\OAuth2Authenticator;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Session\SessionInterface;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Exception\AuthenticationException;
use Symfony\Component\Security\Http\Authenticator\AbstractAuthenticator;
use Symfony\Component\Security\Http\Authenticator\Passport\Badge\UserBadge;
use Symfony\Component\Security\Http\Authenticator\Passport\SelfValidatingPassport;
use Symfony\Component\Security\Http\EntryPoint\AuthenticationEntryPointInterface;

/**
 * @internal
 */
#[CoversClass(OAuth2Authenticator::class)]
final class OAuth2AuthenticatorTest extends TestCase
{
    private UserProvider $userProviderMock;
    private MockObject|OAuth2ClientManager $oauthClientManagerMock;
    private MockObject|OAuth2Authenticator $oAuth2Authenticator;

    protected function setUp(): void
    {
        $this->userProviderMock = $this->createMock(UserProvider::class);
        $this->oauthClientManagerMock = $this->createMock(OAuth2ClientManager::class);
        $this->oAuth2Authenticator = new OAuth2Authenticator($this->userProviderMock, $this->oauthClientManagerMock);
    }

    public function testInitializable(): void
    {
        self::assertInstanceOf(OAuth2Authenticator::class, $this->oAuth2Authenticator);
        self::assertInstanceOf(AbstractAuthenticator::class, $this->oAuth2Authenticator);
        self::assertInstanceOf(AuthenticationEntryPointInterface::class, $this->oAuth2Authenticator);
    }

    public function testSupportsRequestsWithValidOauthSession(): void
    {
        $requestMock = $this->createMock(Request::class);
        $sessionMock = $this->createMock(SessionInterface::class);

        $requestMock->expects(self::exactly(3))
            ->method('getSession')
            ->willReturnOnConsecutiveCalls($sessionMock, $sessionMock, $sessionMock)
        ;
        $sessionMock
            ->expects(self::exactly(3))
            ->method('get')
            ->willReturnOnConsecutiveCalls(
                UserSocialAccountType::SLACK->value,
                'token',
                'user-id',
            )
        ;
        self::assertTrue($this->oAuth2Authenticator->supports($requestMock));
    }

    public function testDoesNotSupportRequestsWithoutOauthSession(): void
    {
        $requestMock = $this->createMock(Request::class);
        $sessionMock = $this->createMock(SessionInterface::class);

        $requestMock->expects(self::exactly(3))
            ->method('getSession')
            ->willReturnOnConsecutiveCalls($sessionMock, $sessionMock, $sessionMock)
        ;
        $sessionMock
            ->expects(self::exactly(3))
            ->method('get')
            ->willReturnOnConsecutiveCalls(
                UserSocialAccountType::SLACK->value,
                'token',
                null
            )
        ;
        self::assertFalse($this->oAuth2Authenticator->supports($requestMock));
    }

    public function testAuthenticatesUserWithOauthData(): void
    {
        $requestMock = $this->createMock(Request::class);
        $sessionMock = $this->createMock(SessionInterface::class);
        $oauthClientMock = $this->createMock(OAuth2Client::class);

        $requestMock
            ->expects(self::exactly(3))
            ->method('getSession')
            ->willReturnOnConsecutiveCalls($sessionMock, $sessionMock, $sessionMock)
        ;
        $sessionMock
            ->expects(self::exactly(3))
            ->method('get')
            ->willReturnOnConsecutiveCalls(
                UserSocialAccountType::SLACK->value,
                'test_token',
                '12345',
            )
        ;
        $this->oauthClientManagerMock->expects(self::once())->method('getByType')->with(UserSocialAccountType::fromString('slack'))->willReturn($oauthClientMock);
        $oauthClientMock
            ->expects(self::once())
            ->method('fetchUser')
            ->with('test_token', '12345')
            ->willReturn(
                new OAuth2ResourceOwner(
                    'user@local.host',
                    'name',
                    'externalUserId'
                )
            )
        ;
        $passport = $this->oAuth2Authenticator->authenticate($requestMock);
        self::assertInstanceOf(SelfValidatingPassport::class, $passport);
        self::assertInstanceOf(UserBadge::class, $passport->getBadge(UserBadge::class));
    }

    public function testHandlesAuthenticationSuccess(): void
    {
        $requestMock = $this->createMock(Request::class);
        $tokenMock = $this->createMock(TokenInterface::class);

        self::assertNull($this->oAuth2Authenticator->onAuthenticationSuccess($requestMock, $tokenMock, 'firewall_name'));
    }

    public function testHandlesAuthenticationFailure(): void
    {
        $requestMock = $this->createMock(Request::class);
        $exceptionMock = $this->createMock(AuthenticationException::class);

        self::assertInstanceOf(RedirectResponse::class, $this->oAuth2Authenticator->onAuthenticationFailure($requestMock, $exceptionMock));
    }

    public function testRedirectsToLoginOnStart(): void
    {
        $requestMock = $this->createMock(Request::class);
        $response = $this->oAuth2Authenticator->start($requestMock);

        self::assertInstanceOf(RedirectResponse::class, $response);
        self::assertSame('/login', $response->getTargetUrl());
    }
}
