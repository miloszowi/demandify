<?php

declare(strict_types=1);

namespace Querify\Tests\Integration\Application\Command\EditEligibleApprovers;

use Querify\Application\Command\EditEligibleApprovers\EditEligibleApprovers;
use Querify\Application\Command\EditEligibleApprovers\EditEligibleApproversHandler;
use Querify\Domain\ExternalService\ExternalServiceConfigurationRepository;
use Querify\Tests\Fixtures\ExternalServiceConfigurationFixture;
use Querify\Tests\integration\BaseKernelTestCase;

/**
 * @internal
 *
 * @covers \Querify\Application\Command\EditEligibleApprovers\EditEligibleApproversHandler
 */
final class EditEligibleApproversHandlerTest extends BaseKernelTestCase
{
    private EditEligibleApproversHandler $handler;
    private ExternalServiceConfigurationRepository $externalServiceConfigurationRepository;

    protected function setUp(): void
    {
        parent::setUp();

        $this->handler = self::getContainer()->get(EditEligibleApproversHandler::class);
        $this->externalServiceConfigurationRepository = self::getContainer()->get(ExternalServiceConfigurationRepository::class);
        $this->load([new ExternalServiceConfigurationFixture()]);
    }

    public function testEditingEliibleApproversIsSuccessful(): void
    {
        $externalServiceConfiguration = $this->externalServiceConfigurationRepository->getByName('querify_postgres');
        $eligibleApproversBeforeEdit = $externalServiceConfiguration->eligibleApprovers;

        $command = new EditEligibleApprovers(
            'querify_postgres',
            []
        );

        $this->handler->__invoke($command);

        $editedExternalServiceConfiguration = $this->externalServiceConfigurationRepository->getByName('querify_postgres');

        self::assertCount(0, $editedExternalServiceConfiguration->eligibleApprovers);
        self::assertNotSame($editedExternalServiceConfiguration->eligibleApprovers, $eligibleApproversBeforeEdit);
    }
}
