<?php

declare(strict_types=1);

namespace Querify\Infrastructure\Controller\OAuth;

use Querify\Application\Command\LinkSocialAccountToUser\LinkSocialAccountToUser;
use Querify\Domain\UserSocialAccount\UserSocialAccountType;
use Querify\Infrastructure\OAuth\Slack\Http\SlackHttpClient;
use Querify\Infrastructure\OAuth\Slack\StateProvider;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Exception\BadRequestException;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Messenger\MessageBusInterface;
use Symfony\Component\Routing\Attribute\Route;

class SlackOAuthController extends AbstractController
{
    public function __construct(
        private readonly StateProvider $stateProvider,
        private readonly SlackHttpClient $slackHttpClient,
        private readonly MessageBusInterface $messageBus
    ) {}

    #[Route('/oauth/slack/connect', methods: ['GET'])]
    public function connect(Request $request): Response
    {
        $code = $request->get('code');
        $state = $request->get('state');

        if (null === $code) {
            throw new BadRequestException('OAuth code is missing');
        }

        if (false === $this->stateProvider->isValidForUser($this->getUser()->getUserIdentifier(), $state)) {
            throw new BadRequestException('State is incorrect.');
        }

        $user = $this->slackHttpClient->oauthAccess($code);
        $this->messageBus->dispatch(
            new LinkSocialAccountToUser(
                $this->getUser()->getUserIdentifier(),
                UserSocialAccountType::SLACK,
                $user->authedUser->id
            )
        );

        return $this->redirect('/profile');
    }
}
