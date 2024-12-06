<?php

namespace Querify\Infrastructure\Api\Provider;

use ApiPlatform\Metadata\Operation;
use ApiPlatform\State\ProviderInterface;
use Querify\Domain\Demand\DemandRepository;
use Querify\Domain\Demand\Exception\DemandNotFoundException;
use Querify\Infrastructure\Api\Resource\Demand;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

class DemandStateProvider implements ProviderInterface
{
    public function __construct(private readonly DemandRepository $demandRepository) {}

    public function provide(Operation $operation, array $uriVariables = [], array $context = []): null|array|object
    {
        try {
            $demand = $this->demandRepository->getByUuid($uriVariables['uuid']);
        } catch (DemandNotFoundException $e) {
            throw new NotFoundHttpException($e->getMessage());
        }

        return Demand::createFromDomainModel($demand);
    }
}
