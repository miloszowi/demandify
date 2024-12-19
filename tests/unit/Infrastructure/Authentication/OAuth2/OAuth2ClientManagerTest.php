<?php

declare(strict_types=1);

namespace Querify\Tests\Unit\Infrastructure\Authentication\OAuth2;

use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Querify\Domain\UserSocialAccount\UserSocialAccountType;
use Querify\Infrastructure\Authentication\OAuth2\OAuth2Client;
use Querify\Infrastructure\Authentication\OAuth2\OAuth2ClientManager;
use Querify\Infrastructure\Authentication\OAuth2\Request\OAuth2AccessRequest;
use Querify\Infrastructure\Authentication\OAuth2\Response\OAuth2AccessResponse;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Session\SessionInterface;

/**
 * @internal
 */
#[CoversClass(OAuth2ClientManager::class)]
final class OAuth2ClientManagerTest extends TestCase
{
    private OAuth2ClientManager $oauthClientManager;
    private MockObject|OAuth2Client $oauthClient;

    protected function setUp(): void
    {
        $this->oauthClient = $this->createMock(OAuth2Client::class);
        $this->oauthClientManager = new OAuth2ClientManager([$this->oauthClient]);
    }

    public function testInitializable(): void
    {
        self::assertInstanceOf(OAuth2ClientManager::class, $this->oauthClientManager);
    }

    public function testShouldGetOAuthClientByRequest(): void
    {
        $oauthRequest = new OAuth2AccessRequest(
            'session-id',
            'state',
            'code',
            new Request()
        );

        $this->oauthClient->expects(self::once())
            ->method('supports')
            ->with('session-id', 'state')
            ->willReturn(true)
        ;

        $result = $this->oauthClientManager->get($oauthRequest);
        self::assertSame($this->oauthClient, $result);
    }

    public function testShouldThrowExceptionIfNoOAuthClientSupportsRequest(): void
    {
        $oauthRequest = new OAuth2AccessRequest(
            'session-id',
            'state',
            'code',
            new Request()
        );

        $this->oauthClient->expects(self::once())
            ->method('supports')
            ->with('session-id', 'state')
            ->willReturn(false)
        ;

        $this->expectException(\LogicException::class);
        $this->oauthClientManager->get($oauthRequest);
    }

    public function testShouldGetOAuthClientByType(): void
    {
        $type = UserSocialAccountType::SLACK;
        $this->oauthClient->expects(self::once())
            ->method('getLinkedSocialAccountType')
            ->willReturn($type)
        ;

        $result = $this->oauthClientManager->getByType($type);
        self::assertSame($this->oauthClient, $result);
    }

    public function testShouldThrowExceptionIfNoOAuthClientFoundByType(): void
    {
        $this->oauthClient->expects(self::once())
            ->method('getLinkedSocialAccountType')
            ->willReturn(UserSocialAccountType::SLACK)
        ;

        $this->expectException(\LogicException::class);
        $this->oauthClientManager->getByType(UserSocialAccountType::GOOGLE);
    }

    public function testShouldAuthorizeOAuthClient(): void
    {
        $response = new OAuth2AccessResponse(
            'access-token',
            'external-user-id'
        );

        $this->oauthClient->expects(self::once())
            ->method('getLinkedSocialAccountType')
            ->willReturn(UserSocialAccountType::SLACK)
        ;
        $this->oauthClient->expects(self::once())
            ->method('supports')
            ->with('session-id', 'state')
            ->willReturn(true)
        ;
        $this->oauthClient->expects(self::once())
            ->method('authorize')
            ->with('code')
            ->willReturn($response)
        ;

        $sessionMock = $this->createMock(SessionInterface::class);
        $request = $this->createMock(Request::class);
        $request->expects(self::exactly(3))
            ->method('getSession')
            ->willReturnOnConsecutiveCalls(
                $sessionMock,
                $sessionMock,
                $sessionMock,
            )
        ;

        $oauthRequest = new OAuth2AccessRequest('session-id', 'state', 'code', $request);
        $result = $this->oauthClientManager->authorize($oauthRequest);

        self::assertSame($response, $result);
    }

    public function testShouldThrowExceptionIfNoOAuthClientSupportsAuthorize(): void
    {
        $this->oauthClient->expects(self::once())
            ->method('supports')
            ->with('session-id', 'state')
            ->willReturn(false)
        ;

        $oauthRequest = new OAuth2AccessRequest('session-id', 'state', 'code', new Request());

        $this->expectException(\LogicException::class);
        $this->oauthClientManager->authorize($oauthRequest);
    }

    public function testShouldStartOAuthFlow(): void
    {
        $type = UserSocialAccountType::SLACK;
        $this->oauthClient->expects(self::once())
            ->method('getLinkedSocialAccountType')
            ->willReturn($type)
        ;
        $this->oauthClient->expects(self::once())
            ->method('start')
            ->with(self::isInstanceOf(Request::class))
            ->willReturn(new RedirectResponse('http://example.com'))
        ;

        $request = $this->createMock(Request::class);
        $result = $this->oauthClientManager->start($request, $type);

        self::assertInstanceOf(RedirectResponse::class, $result);
    }

    public function testShouldThrowExceptionIfNoOAuthClientSupportsStart(): void
    {
        $this->oauthClient->expects(self::once())
            ->method('getLinkedSocialAccountType')
            ->willReturn(UserSocialAccountType::SLACK)
        ;

        $request = $this->createMock(Request::class);

        $this->expectException(\LogicException::class);
        $this->oauthClientManager->start($request, UserSocialAccountType::GOOGLE);
    }
}
