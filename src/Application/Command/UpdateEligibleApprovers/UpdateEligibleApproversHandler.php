<?php

declare(strict_types=1);

namespace Demandify\Application\Command\UpdateEligibleApprovers;

use Demandify\Application\Command\CommandHandler;
use Demandify\Domain\ExternalService\ExternalServiceConfiguration;
use Demandify\Domain\ExternalService\ExternalServiceConfigurationRepository;
use Ramsey\Uuid\UuidInterface;

class UpdateEligibleApproversHandler implements CommandHandler
{
    public function __construct(private readonly ExternalServiceConfigurationRepository $externalServiceConfigurationRepository) {}

    public function __invoke(UpdateEligibleApprovers $command): void
    {
        $configuration = $this->externalServiceConfigurationRepository->findByName($command->externalServiceName);
        $desiredUserUuidsState = array_map(static fn (UuidInterface $uuid) => $uuid->toString(), $command->desiredUserUuidsState);

        if (null !== $configuration) {
            $configuration->eligibleApprovers = $desiredUserUuidsState;
        } else {
            $configuration = new ExternalServiceConfiguration($command->externalServiceName, $desiredUserUuidsState);
        }

        $this->externalServiceConfigurationRepository->save($configuration);
    }
}
