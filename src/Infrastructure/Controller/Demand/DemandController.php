<?php

declare(strict_types=1);

namespace Demandify\Infrastructure\Controller\Demand;

use Demandify\Application\Command\ApproveDemand\ApproveDemand;
use Demandify\Application\Command\CommandBus;
use Demandify\Application\Command\DeclineDemand\DeclineDemand;
use Demandify\Application\Query\GetDemandsAwaitingDecisionForUser\GetDemandsAwaitingDecisionForUser;
use Demandify\Application\Query\GetDemandsSubmittedByUser\GetDemandsSubmittedByUser;
use Demandify\Application\Query\QueryBus;
use Demandify\Domain\Demand\Demand;
use Demandify\Domain\User\User;
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
    #[IsGranted('view', subject: 'demand')]
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

        $result = $this->queryBus->ask(new GetDemandsSubmittedByUser(
            $user->uuid,
            $page,
            $limit,
            $search
        ));

        return $this->render('demand/user_demands.html.twig', $result);
    }

    #[Route(
        path: '/review-demands',
        name: 'app_review_demands',
        methods: [Request::METHOD_GET],
    )]
    public function reviewDemands(Request $request): Response
    {
        /** @var User $user */
        $user = $this->getUser();

        $demands = $this->queryBus->ask(new GetDemandsAwaitingDecisionForUser($user->uuid));

        return $this->render('demand/review_demands.html.twig', ['demands' =>$demands]);
    }

    #[Route(
        path: '/demand/{id}/approve',
        name: 'app_demand_approve',
        requirements: ['id' => Requirement::UUID],
        methods: [Request::METHOD_POST],
    )]
    public function approveDemand(Demand $demand): Response
    {
        $this->commandBus->dispatch(
            new ApproveDemand($demand->uuid, $this->getUser())
        );

        return $this->redirectToRoute('app_review_demands');
    }

    #[Route(
        path: '/demand/{id}/decline',
        name: 'app_demand_decline',
        requirements: ['id' => Requirement::UUID],
        methods: [Request::METHOD_POST],
    )]
    public function declineDemand(Demand $demand): Response
    {
        $this->commandBus->dispatch(
            new DeclineDemand($demand->uuid, $this->getUser())
        );

        return $this->redirectToRoute('app_review_demands');
    }
}
