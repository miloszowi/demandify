<?php

declare(strict_types=1);

namespace Querify\Application\Command\UpdateEligibleApprovers;

use Querify\Domain\ExternalService\ExternalServiceConfiguration;
use Querify\Domain\ExternalService\ExternalServiceConfigurationRepository;
use Symfony\Component\Messenger\Attribute\AsMessageHandler;

#[AsMessageHandler]
class UpdateEligibleApproversHandler
{
    public function __construct(private readonly ExternalServiceConfigurationRepository $externalServiceConfigurationRepository) {}

    public function __invoke(UpdateEligibleApprovers $command): void
    {
        $configuration = $this->externalServiceConfigurationRepository->findByName($command->externalServiceName);

        if (null !== $configuration) {
            $configuration->eligibleApprovers = $command->desiredUserUuidsState;
        } else {
            $configuration = new ExternalServiceConfiguration($command->externalServiceName, $command->desiredUserUuidsState);
        }

        $this->externalServiceConfigurationRepository->save($configuration);
    }
}
