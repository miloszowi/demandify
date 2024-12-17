<?php

declare(strict_types=1);

namespace spec\Querify\Application\Command\EditEligibleApprovers;

use PhpSpec\ObjectBehavior;
use Prophecy\Argument;
use Querify\Application\Command\EditEligibleApprovers\EditEligibleApprovers;
use Querify\Application\Command\EditEligibleApprovers\EditEligibleApproversHandler;
use Querify\Domain\ExternalService\ExternalServiceConfiguration;
use Querify\Domain\ExternalService\ExternalServiceConfigurationRepository;
use Ramsey\Uuid\Uuid;

class EditEligibleApproversHandlerSpec extends ObjectBehavior
{
    public function let(
        ExternalServiceConfigurationRepository $externalServiceConfigurationRepository
    ): void {
        $this->beConstructedWith($externalServiceConfigurationRepository);
    }

    public function it_is_initializable(): void
    {
        $this->shouldHaveType(EditEligibleApproversHandler::class);
    }

    public function it_updates_existing_configuration(
        ExternalServiceConfigurationRepository $externalServiceConfigurationRepository,
    ): void {
        $command = new EditEligibleApprovers(
            'test_service',
            [Uuid::uuid4(), Uuid::uuid4()],
        );
        $externalServiceConfiguration = new ExternalServiceConfiguration(
            $command->externalServiceName,
            []
        );
        $externalServiceConfigurationRepository->findByName($command->externalServiceName)->willReturn($externalServiceConfiguration);
        $externalServiceConfigurationRepository->save($externalServiceConfiguration)->shouldBeCalled();

        $this->__invoke($command);
    }

    public function it_creates_new_configuration_when_not_found(
        ExternalServiceConfigurationRepository $externalServiceConfigurationRepository,
    ): void {
        $command = new EditEligibleApprovers(
            'test_service',
            [Uuid::uuid4(), Uuid::uuid4()],
        );

        $externalServiceConfigurationRepository->findByName($command->externalServiceName)->willReturn(null);
        $externalServiceConfigurationRepository->save(Argument::type(ExternalServiceConfiguration::class))->shouldBeCalled();

        $this->__invoke($command);
    }
}
