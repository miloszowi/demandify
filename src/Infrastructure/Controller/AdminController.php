<?php

declare(strict_types=1);

namespace Demandify\Infrastructure\Controller;

use Demandify\Application\Command\CommandBus;
use Demandify\Application\Command\UpdateEligibleApprovers\UpdateEligibleApprovers;
use Demandify\Domain\ExternalService\Exception\ExternalServiceNotFoundException;
use Demandify\Domain\ExternalService\ExternalServiceConfigurationRepository;
use Demandify\Domain\ExternalService\ExternalServiceRepository;
use Demandify\Domain\User\User;
use Demandify\Domain\User\UserRepository;
use Demandify\Infrastructure\Symfony\Form\ExternalServiceConfiguration\ExternalServiceConfiguration;
use Demandify\Infrastructure\Symfony\Form\ExternalServiceConfiguration\ExternalServiceConfigurationFormType;
use Ramsey\Uuid\Uuid;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

class AdminController extends AbstractController
{
    public function __construct(
        private readonly ExternalServiceRepository $externalServiceRepository,
        private readonly ExternalServiceConfigurationRepository $externalServiceConfigurationRepository,
        private readonly UserRepository $userRepository,
        private readonly CommandBus $commandBus,
    ) {}

    #[Route('/admin', name: 'app_admin', methods: ['GET'])]
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
            $this->commandBus->dispatch(
                new UpdateEligibleApprovers(
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

            return $this->redirectToRoute('app_admin');
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
        $eligibleApprovers = array_map(
            static fn (string $eligibleApprover) => Uuid::fromString($eligibleApprover),
            $service->eligibleApprovers ?? []
        );

        if (null === $service) {
            return [];
        }

        return $this->userRepository->findByUuids($eligibleApprovers);
    }
}
