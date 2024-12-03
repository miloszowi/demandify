<?php

declare(strict_types=1);

namespace Querify\Infrastructure\Controller;

use Doctrine\DBAL\Connection;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

class SystemController extends AbstractController
{
    #[Route('/_system/health', name: 'healthcheck')]
    public function health(Connection $connection): Response
    {
        return new JsonResponse(
            [
                'database' => $connection->connect(),
                'redis' => true,
                'rabbitmq' => true,
            ]
        );
    }

    #[Route('/_system/metrics', name: 'metrics')]
    public function metrics(): Response
    {
        return new JsonResponse([]);
    }
}