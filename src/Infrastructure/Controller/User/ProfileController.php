<?php

declare(strict_types=1);

namespace Querify\Infrastructure\Controller\User;

use Querify\Infrastructure\OAuth\Slack\SlackConfiguration;
use Querify\Infrastructure\OAuth\Slack\StateProvider;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

class ProfileController extends AbstractController
{
    public function __construct(
        private readonly StateProvider $slackStateProvider,
        private readonly SlackConfiguration $slackConfiguration
    ) {}

    #[Route('/profile', name: 'app_profile', methods: ['GET'])]
    public function index(): Response
    {
        return $this->render('user/index.html.twig', [
            'user' => $this->getUser(),
            'slack_client_id' => $this->slackConfiguration->clientId,
            'slack_oauth_redirect_uri' => $this->slackConfiguration->oauthRedirectUri,
            'state' => $this->slackStateProvider->provideForUser($this->getUser()->getUserIdentifier()),
        ]);
    }
}
