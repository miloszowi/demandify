<?php

declare(strict_types=1);

namespace Demandify\Infrastructure\Controller\Demand;

use Demandify\Application\Command\ApproveDemand\ApproveDemand;
use Demandify\Application\Command\CommandBus;
use Demandify\Application\Command\DeclineDemand\DeclineDemand;
use Demandify\Application\Query\GetDemandsSubmittedByUser\GetDemandsSubmittedByUser;
use Demandify\Application\Query\GetDemandsToBeReviewedForUser\GetDemandsToBeReviewedForUser;
use Demandify\Application\Query\QueryBus;
use Demandify\Application\Query\ReadModel\DemandsSubmittedByUser;
use Demandify\Domain\Demand\Demand;
use Demandify\Domain\User\User;
use Demandify\Infrastructure\Authentication\Voter\DemandVoter;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Routing\Requirement\Requirement;
use Symfony\Component\Security\Http\Attribute\IsGranted;

class DemandController extends AbstractController
{
    public function __construct(
        private readonly QueryBus $queryBus,
        private readonly CommandBus $commandBus,
    ) {}

    #[Route(
        path: '/demand/{id}',
        name: 'app_demand_view',
        requirements: ['id' => Requirement::UUID],
        methods: [Request::METHOD_GET],
    )]
    #[IsGranted(DemandVoter::VIEW, subject: 'demand')]
    public function view(Demand $demand): Response
    {
        return $this->render('demand/view.html.twig', [
            'demand' => $demand,
        ]);
    }

    #[Route(
        path: '/demands',
        name: 'app_demands',
        methods: [Request::METHOD_GET],
    )]
    public function userDemands(Request $request): Response
    {
        /** @var User $user */
        $user = $this->getUser();
        $page = max(1, (int) $request->query->get('page', 1));
        $limit = min(50, max(1, (int) $request->query->get('limit', 10)));
        $search = $request->query->get('search');

        /** @var DemandsSubmittedByUser */
        $result = $this->queryBus->ask(new GetDemandsSubmittedByUser(
            $user->uuid,
            $page,
            $limit,
            $search
        ));

        return $this->render('demand/user_demands.html.twig', [
            'demands' => $result->demands,
            'total' => $result->total,
            'page' => $result->page,
            'limit' => $result->limit,
            'totalPages' => $result->totalPages,
            'search' => $result->search,
        ]);
    }

    #[Route(
        path: '/demands/review',
        name: 'app_review_demands',
        methods: [Request::METHOD_GET],
    )]
    public function reviewDemands(): Response
    {
        /** @var User $user */
        $user = $this->getUser();

        $demands = $this->queryBus->ask(new GetDemandsToBeReviewedForUser($user->uuid));

        return $this->render('demand/review_demands.html.twig', ['demands' => $demands]);
    }

    #[Route(
        path: '/demands/{id}/approve',
        name: 'app_demand_approve',
        requirements: ['id' => Requirement::UUID],
        methods: [Request::METHOD_POST],
    )]
    #[IsGranted(DemandVoter::DECISION, subject: 'demand')]
    public function approveDemand(Demand $demand): Response
    {
        /** @var User $user */
        $user = $this->getUser();
        $this->commandBus->dispatch(
            new ApproveDemand($demand->uuid, $user->uuid)
        );

        return $this->redirectToRoute('app_review_demands');
    }

    #[Route(
        path: '/demands/{id}/decline',
        name: 'app_demand_decline',
        requirements: ['id' => Requirement::UUID],
        methods: [Request::METHOD_POST],
    )]
    #[IsGranted(DemandVoter::DECISION, subject: 'demand')]
    public function declineDemand(Demand $demand): Response
    {
        /** @var User $user */
        $user = $this->getUser();
        $this->commandBus->dispatch(
            new DeclineDemand($demand->uuid, $user->uuid)
        );

        return $this->redirectToRoute('app_review_demands');
    }
}
