<?php

declare(strict_types=1);

namespace Demandify\Infrastructure\Controller;

use Demandify\Domain\UserSocialAccount\UserSocialAccountType;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

class SecurityController extends AbstractController
{
    #[Route(path: '/login', name: 'app_login')]
    public function login(): Response
    {
        return $this->render('security/login.html.twig', [
            'social_account_types' => UserSocialAccountType::cases(),
        ]);
    }
}
