<?php

declare(strict_types=1);

namespace Demandify\Infrastructure\Controller\Demand;

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
    public function __construct(private readonly QueryBus $queryBus) {}

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
        name: 'app_demand_user_demands',
        methods: [Request::METHOD_GET],
        condition: 'request.get("page") > 0',
    )]
    public function userDemands(Request $request): Response
    {
        /** @var User $user */
        $user = $this->getUser();
        $page = (int) $request->get('page', 1);

        $demands = $this->queryBus->ask(new GetDemandsSubmittedByUser($user->uuid, $page, 50));

        return $this->render('demand/user_demands.html.twig', [
            'demands' => $demands,
        ]);
    }
}
