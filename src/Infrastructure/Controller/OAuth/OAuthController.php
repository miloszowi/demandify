<?php

declare(strict_types=1);

namespace Querify\Infrastructure\Controller\OAuth;

use Querify\Infrastructure\ExternalServices\OAuth\OAuthManager;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Exception\BadRequestException;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

class OAuthController extends AbstractController
{
    public function __construct(private readonly OAuthManager $clientManager) {}

    #[Route('/oauth/slack/connect', methods: ['GET'])]
    public function connect(Request $request): Response
    {
        $code = $request->get('code');
        $state = $request->get('state');

        if (null === $code) {
            throw new BadRequestException('OAuth code is missing');
        }

        if (null === $state) {
            throw new BadRequestException('State is incorrect.');
        }

        $this->clientManager->handle(
            $code,
            $state,
            $this->getUser()->getUserIdentifier()
        );

        return $this->redirect('/profile');
    }
}
