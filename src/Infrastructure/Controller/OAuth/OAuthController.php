<?php

declare(strict_types=1);

namespace Demandify\Infrastructure\Controller\OAuth;

use Demandify\Domain\UserSocialAccount\UserSocialAccountType;
use Demandify\Infrastructure\Authentication\AccessToken;
use Demandify\Infrastructure\Authentication\OAuth2\Provider\OAuth2ClientResolver;
use Demandify\Infrastructure\Event\UserIdentityAuthorized\UserIdentityAuthorized;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Exception\BadRequestException;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Messenger\MessageBusInterface;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Component\Routing\Requirement\EnumRequirement;

class OAuthController extends AbstractController
{
    public function __construct(
        private readonly OAuth2ClientResolver $clientResolver,
        private readonly MessageBusInterface $messageBus
    ) {}

    #[Route(
        path: '/oauth/{type}',
        name: 'app_oauth_start',
        requirements: ['type' => new EnumRequirement(UserSocialAccountType::class)],
        methods: [Request::METHOD_GET],
    )]
    public function start(Request $request, string $type): RedirectResponse
    {
        $client = $this->clientResolver->byType(UserSocialAccountType::fromString($type));
        $state = $this->generateStateString($request->getSession()->getId(), $type);
        $request->getSession()->set('state', $state);

        return new RedirectResponse(
            $client->getAuthorizationUrl($state, $this->generateCheckUrl($type))
        );
    }

    #[Route(
        path: '/oauth/{type}/check',
        name: 'app_oauth_check',
        requirements: ['type' => new EnumRequirement(UserSocialAccountType::class)],
        methods: [Request::METHOD_GET],
        condition: 'request.get("code") !== null && request.get("state") !== null',
    )]
    public function check(Request $request, string $type): RedirectResponse
    {
        $code = $request->get('code');
        $state = $request->get('state');
        $socialAccountType = UserSocialAccountType::fromString($type);
        $storedState = $request->getSession()->get('state');

        if ($storedState !== $state) {
            throw new BadRequestException('Invalid state.');
        }

        $client = $this->clientResolver->byType($socialAccountType);
        $userIdentity = $client->fetchUser($code, $this->generateCheckUrl($type));

        $this->messageBus->dispatch(
            new UserIdentityAuthorized(
                $userIdentity->type,
                $userIdentity->email,
                $userIdentity->externalUserId,
                $userIdentity->extraData
            )
        );

        $request->getSession()->set(
            AccessToken::class,
            $userIdentity->accessToken
        );

        $this->addFlash('success', 'You have been successfully authorized.');

        return $this->redirect($this->generateUrl('app_home'));
    }

    private function generateCheckUrl(string $type): string
    {
        return $this->generateUrl(
            route: 'app_oauth_check',
            parameters: ['type' => $type],
            referenceType: UrlGeneratorInterface::ABSOLUTE_URL,
        );
    }

    private function generateStateString(string $sessionId, string $type): string
    {
        return hash(
            'sha256',
            \sprintf('%s|%s', $sessionId, $type),
        );
    }
}
