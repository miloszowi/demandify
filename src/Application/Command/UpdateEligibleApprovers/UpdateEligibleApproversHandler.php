<?php

declare(strict_types=1);

namespace Demandify\Application\Command\UpdateEligibleApprovers;

use Demandify\Application\Command\CommandHandler;
use Demandify\Domain\ExternalService\ExternalServiceConfiguration;
use Demandify\Domain\ExternalService\ExternalServiceConfigurationRepository;

class UpdateEligibleApproversHandler implements CommandHandler
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
