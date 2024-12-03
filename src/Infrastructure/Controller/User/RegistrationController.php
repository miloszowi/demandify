<?php

declare(strict_types=1);

namespace Querify\Infrastructure\Controller\User;

use Querify\Application\Command\RegisterUser\RegisterUser;
use Querify\Domain\User\Email;
use Querify\Infrastructure\Form\User\RegistrationFormType;
use Querify\Infrastructure\Form\User\User;
use Querify\Infrastructure\Repository\UserRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Messenger\MessageBusInterface;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Routing\Attribute\Route;

class RegistrationController extends AbstractController
{
    public function __construct(
        private readonly MessageBusInterface $messageBus,
        private readonly UserRepository $userRepository
    ) {
    }

    #[Route('/register', name: 'app_register')]
    public function register(Request $request, UserPasswordHasherInterface $userPasswordHasher, Security $security): Response
    {
        $user = new User;
        $form = $this->createForm(RegistrationFormType::class, $user);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $email = $form->get('email')->getData();

            $this->messageBus->dispatch(
                new RegisterUser(
                    $email,
                    $form->get('plainPassword')->getData(),
                    $form->get('firstName')->getData(),
                    $form->get('lastName')->getData(),
                    [],
                )
            );

            return $security->login(
                $this->userRepository->getByEmail(Email::fromString($email)),
                'form_login',
                'main'
            );
        }

        return $this->render('registration/register.html.twig', [
            'registrationForm' => $form,
        ]);
    }
}
