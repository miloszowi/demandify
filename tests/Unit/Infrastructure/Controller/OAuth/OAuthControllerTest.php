<?php

declare(strict_types=1);

namespace Demandify\Tests\Unit\Infrastructure\Controller\OAuth;

use Demandify\Domain\UserSocialAccount\UserSocialAccountType;
use Demandify\Infrastructure\Authentication\AccessToken;
use Demandify\Infrastructure\Authentication\OAuth2\Provider\OAuth2Client;
use Demandify\Infrastructure\Authentication\OAuth2\Provider\OAuth2ClientResolver;
use Demandify\Infrastructure\Authentication\OAuth2\Response\OAuth2Identity;
use Demandify\Infrastructure\Controller\OAuth\OAuthController;
use Demandify\Infrastructure\Event\UserIdentityAuthorized\UserIdentityAuthorized;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Constraint\IsInstanceOf;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Session\Session;
use Symfony\Component\Messenger\Envelope;
use Symfony\Component\Messenger\MessageBusInterface;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;

/**
 * @internal
 */
#[CoversClass(OAuthController::class)]
final class OAuthControllerTest extends TestCase
{
    private OAuthController $controller;
    private MockObject&OAuth2ClientResolver $oauthClientResolverMock;
    private MessageBusInterface&MockObject $messageBusMock;
    private MockObject&UrlGeneratorInterface $urlGeneratorMock;

    protected function setUp(): void
    {
        $this->oauthClientResolverMock = $this->createMock(OAuth2ClientResolver::class);
        $this->messageBusMock = $this->createMock(MessageBusInterface::class);
        $this->urlGeneratorMock = $this->createMock(UrlGeneratorInterface::class);
        $this->controller = new OAuthController(
            $this->oauthClientResolverMock,
            $this->messageBusMock,
            $this->urlGeneratorMock,
        );
    }

    public function testCheck(): void
    {
        $request = $this->createMock(Request::class);

        $request
            ->expects(self::exactly(2))
            ->method('get')
            ->willReturnOnConsecutiveCalls('code', 'state')
        ;

        $session = $this->createMock(Session::class);
        $session
            ->expects(self::once())
            ->method('get')
            ->with('state')
            ->willReturn('state')
        ;

        $request
            ->expects(self::exactly(2))
            ->method('getSession')
            ->willReturn($session)
        ;

        $this->urlGeneratorMock
            ->expects(self::exactly(2))
            ->method('generate')
            ->willReturnOnConsecutiveCalls(
                'http://localhost/oauth/slack/check',
                'http://localhost/'
            )
        ;

        $slackClientMock = $this->createMock(OAuth2Client::class);
        $slackClientMock
            ->expects(self::once())
            ->method('fetchUser')
            ->willReturn(
                new OAuth2Identity(
                    UserSocialAccountType::SLACK,
                    $this->createMock(AccessToken::class),
                    'test@local.host',
                    'external-id'
                )
            )
        ;
        $this->oauthClientResolverMock
            ->expects(self::once())
            ->method('byType')
            ->with(UserSocialAccountType::SLACK)
            ->willReturn($slackClientMock)
        ;

        $this->messageBusMock
            ->expects(self::once())
            ->method('dispatch')
            ->with(new IsInstanceOf(UserIdentityAuthorized::class))
            ->willReturnCallback(static function ($message) {
                return new Envelope($message);
            })
        ;

        $result = $this->controller->check($request, 'slack');

        self::assertInstanceOf(RedirectResponse::class, $result);
    }
}
