<?php

declare(strict_types=1);

namespace Querify\Infrastructure\Controller;

use Querify\Application\Command\EditEligibleApprovers\EditEligibleApprovers;
use Querify\Domain\ExternalService\Exception\ExternalServiceNotFoundException;
use Querify\Domain\ExternalService\ExternalServiceConfigurationRepository;
use Querify\Domain\ExternalService\ExternalServiceRepository;
use Querify\Domain\User\User;
use Querify\Domain\User\UserRepository;
use Querify\Infrastructure\Symfony\Form\ExternalServiceConfiguration\ExternalServiceConfiguration;
use Querify\Infrastructure\Symfony\Form\ExternalServiceConfiguration\ExternalServiceConfigurationFormType;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Messenger\MessageBusInterface;
use Symfony\Component\Routing\Attribute\Route;

class AdminController extends AbstractController
{
    public function __construct(
        private readonly ExternalServiceRepository $externalServiceRepository,
        private readonly ExternalServiceConfigurationRepository $externalServiceConfigurationRepository,
        private readonly UserRepository $userRepository,
        private readonly MessageBusInterface $messageBus,
    ) {}

    #[Route('/admin', name: 'admin_index', methods: ['GET'])]
    public function index(): Response
    {
        return $this->render(
            'admin/index.html.twig',
            [
                'external_services' => $this->externalServiceRepository->getAll(),
            ]
        );
    }

    #[Route('/admin/services/{service}', methods: ['GET', 'POST'])]
    public function editService(Request $request, string $service): Response
    {
        try {
            $service = $this->externalServiceRepository->getByName($service);
        } catch (ExternalServiceNotFoundException) {
            return new Response('service not found', Response::HTTP_NOT_FOUND);
        }

        $externalServiceConfiguration = new ExternalServiceConfiguration($service->name);
        $externalServiceConfiguration->eligibleApprovers = $this->getEligibleApprovers($service->name);

        $form = $this->createForm(ExternalServiceConfigurationFormType::class, $externalServiceConfiguration);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $this->messageBus->dispatch(
                new EditEligibleApprovers(
                    $service->name,
                    array_map(
                        static fn (User $user) => $user->uuid,
                        $form->get('eligibleApprovers')->getData()
                    ),
                )
            );

            $this->addFlash(
                'success',
                \sprintf('Succesfully edited %s service', $service->name)
            );

            return $this->redirectToRoute('admin_index');
        }

        return $this->render(
            'admin/edit_service.html.twig',
            [
                'form' => $form,
            ]
        );
    }

    /**
     * @return User[]
     */
    private function getEligibleApprovers(string $service): array
    {
        // todo - make this execute in one query
        $service = $this->externalServiceConfigurationRepository->findByName($service);

        if (null === $service) {
            return [];
        }

        return $this->userRepository->findByUuids($service->eligibleApprovers);
    }
}
