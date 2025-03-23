<?php

declare(strict_types=1);

namespace Demandify\Infrastructure\Controller;

use Demandify\Application\Command\CommandBus;
use Demandify\Application\Command\SubmitDemand\SubmitDemand;
use Demandify\Domain\ExternalService\ExternalServiceRepository;
use Demandify\Infrastructure\Symfony\Form\Demand\Demand;
use Demandify\Infrastructure\Symfony\Form\Demand\DemandFormType;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

class HomeController extends AbstractController
{
    public function __construct(
        private readonly CommandBus $commandBus,
        private readonly ExternalServiceRepository $externalServiceRepository,
    ) {}

    #[Route('/', name: 'app_home')]
    public function index(Request $request): Response
    {
        $demand = new Demand();
        $form = $this->createForm(DemandFormType::class, $demand);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $this->commandBus->dispatch(
                new SubmitDemand(
                    $this->getUser()->getUserIdentifier(),
                    $form->get('service')->getData(),
                    $form->get('content')->getData(),
                    $form->get('reason')->getData(),
                )
            );

            return $this->redirectToRoute('app_home');
        }

        return $this->render('index.html.twig', [
            'demandForm' => $form,
            'external_services' => $this->externalServiceRepository->getAll(),
        ]);
    }
}
