<?php

declare(strict_types=1);

namespace Querify\Infrastructure\Controller\User;

use Querify\Domain\User\Email;
use Querify\Domain\UserSocialAccount\UserSocialAccountType;
use Querify\Infrastructure\ExternalServices\OAuth\OAuthManager;
use Querify\Infrastructure\Repository\UserSocialAccountRepository;
use Querify\Infrastructure\Twig\OAuthFrontFriendlyHandler;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

class ProfileController extends AbstractController
{
    public function __construct(
        private OAuthManager $clientManager,
        private UserSocialAccountRepository $userSocialAccountRepository
    ) {}

    #[Route('/profile', name: 'app_profile', methods: ['GET'])]
    public function index(): Response
    {
        $this->getOAuthHandlers();

        return $this->render('user/index.html.twig', [
            'user' => $this->getUser(),
            'oauth_handlers' => $this->getOAuthHandlers(),
        ]);
    }

    /**
     * @return OAuthFrontFriendlyHandler[]
     */
    private function getOAuthHandlers(): array
    {
        $handlers = [];

        foreach ($this->clientManager->getOAuthHandlers() as $oauthHandler) {
            $handlers[] = new OAuthFrontFriendlyHandler(
                $oauthHandler->getName(),
                $oauthHandler->getOauthUrl(),
                (bool) $this->userSocialAccountRepository->findByEmailAndType(
                    Email::fromString($this->getUser()->getUserIdentifier()),
                    UserSocialAccountType::from($oauthHandler->getName())
                )
            );
        }

        return $handlers;
    }
}
