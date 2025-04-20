<?php

declare(strict_types=1);

namespace Demandify\Infrastructure\Controller\User;

use Demandify\Application\Command\CommandBus;
use Demandify\Application\Command\UpdateSocialAccountNotifiability\UpdateSocialAccountNotifiability;
use Demandify\Domain\User\User;
use Demandify\Domain\UserSocialAccount\UserSocialAccountType;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Routing\Requirement\EnumRequirement;

class ProfileController extends AbstractController
{
    public function __construct(private readonly CommandBus $commandBus) {}

    #[Route('/profile', name: 'app_profile', methods: ['GET'])]
    public function index(): Response
    {
        return $this->render('user/index.html.twig', [
            'user' => $this->getUser(),
            'social_account_types' => UserSocialAccountType::cases(),
        ]);
    }

    #[Route(
        '/profile/social-account/{type}/notifiability',
        name: 'app_profile_toggle_social_account_notifiability',
        requirements: ['type' => new EnumRequirement(UserSocialAccountType::class)],
        methods: ['POST']
    )]
    public function toggleSocialAccountNotifiability(Request $request, string $type): RedirectResponse
    {
        /** @var User $user */
        $user = $this->getUser();

        $this->commandBus->dispatch(
            new UpdateSocialAccountNotifiability(
                $user->uuid,
                UserSocialAccountType::fromString($type),
                $request->request->getBoolean('notifiability')
            )
        );

        return new RedirectResponse($this->generateUrl('app_profile'));
    }
}
