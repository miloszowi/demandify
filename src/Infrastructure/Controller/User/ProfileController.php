<?php

declare(strict_types=1);

namespace Demandify\Infrastructure\Controller\User;

use Demandify\Domain\UserSocialAccount\UserSocialAccountType;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

class ProfileController extends AbstractController
{
    #[Route('/profile', name: 'app_profile', methods: ['GET'])]
    public function index(): Response
    {
        return $this->render('user/index.html.twig', [
            'user' => $this->getUser(),
            'social_account_types' => UserSocialAccountType::cases(),
        ]);
    }
}
