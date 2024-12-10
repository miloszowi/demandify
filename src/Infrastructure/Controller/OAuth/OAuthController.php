<?php

declare(strict_types=1);

namespace Querify\Infrastructure\Controller\OAuth;

use Querify\Application\Command\LinkSocialAccountToUser\LinkSocialAccountToUser;
use Querify\Domain\UserSocialAccount\UserSocialAccountType;
use Querify\Infrastructure\Authentication\OAuth2Authenticator;
use Querify\Infrastructure\ExternalServices\OAuth2\OAuth2ClientManager;
use Querify\Infrastructure\ExternalServices\OAuth2\Request\OAuth2AccessRequest;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Component\HttpFoundation\Exception\BadRequestException;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Messenger\MessageBusInterface;
use Symfony\Component\Routing\Attribute\Route;

class OAuthController extends AbstractController
{
    public function __construct(
        private readonly OAuth2ClientManager $oauthClientManager,
        private MessageBusInterface $messageBus,
    ) {}

    #[Route('/oauth/{type}', name: 'app_oauth', methods: ['GET'])]
    public function start(Request $request, string $type): Response
    {
        $request->getSession()->set(OAuth2Authenticator::OAUTH_ACCESS_PROVIDER_KEY, $type);

        return $this->oauthClientManager->start(
            $request,
            UserSocialAccountType::fromString($type)
        );
    }

    #[Route('/oauth/{type}/authorize', name: 'app_oauth_authorize', methods: ['GET'])]
    public function authorize(Request $request, Security $security, string $type): Response
    {
        $code = $request->get('code');
        $state = $request->get('state');

        if (null === $code) {
            throw new BadRequestException('OAuth2 code is missing');
        }

        if (null === $state) {
            throw new BadRequestException('State is incorrect.');
        }

        $client = $this->oauthClientManager->getByType(UserSocialAccountType::fromString($type));
        $authorizationResponse = $this->oauthClientManager->authorize(
            new OAuth2AccessRequest(
                $request->getSession()->getId(),
                $state,
                $code,
                $request
            )
        );
        $user = $client->fetchUser(
            $authorizationResponse->accessToken,
            $authorizationResponse->externalUserId
        );

        $this->messageBus->dispatch(
            new LinkSocialAccountToUser(
                $user->email,
                $user->name,
                UserSocialAccountType::fromString($type),
                $user->externalUserId,
                $user->extraData
            )
        );

        return $this->redirect('/profile');
    }
}
