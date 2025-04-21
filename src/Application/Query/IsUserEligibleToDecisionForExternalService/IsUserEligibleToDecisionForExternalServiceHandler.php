<?php

declare(strict_types=1);

namespace Demandify\Application\Query\IsUserEligibleToDecisionForExternalService;

use Demandify\Application\Query\QueryHandler;
use Demandify\Domain\ExternalService\ExternalServiceConfigurationRepository;

class IsUserEligibleToDecisionForExternalServiceHandler implements QueryHandler
{
    public function __construct(private readonly ExternalServiceConfigurationRepository $externalServiceConfigurationRepository) {}

    public function __invoke(IsUserEligibleToDecisionForExternalService $query): bool
    {
        $externalServiceConfiguration = $this->externalServiceConfigurationRepository->getByName($query->externalServiceName);

        return $externalServiceConfiguration->isUserEligible($query->user);
    }
}
