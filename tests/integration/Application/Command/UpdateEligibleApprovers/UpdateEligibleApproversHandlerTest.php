<?php

declare(strict_types=1);

namespace Querify\Tests\Integration\Application\Command\UpdateEligibleApprovers;

use Querify\Application\Command\UpdateEligibleApprovers\UpdateEligibleApprovers;
use Querify\Application\Command\UpdateEligibleApprovers\UpdateEligibleApproversHandler;
use Querify\Domain\ExternalService\ExternalServiceConfigurationRepository;
use Querify\Tests\Fixtures\ExternalServiceConfigurationFixture;
use Querify\Tests\integration\BaseKernelTestCase;

/**
 * @internal
 *
 * @covers \Querify\Application\Command\UpdateEligibleApprovers\UpdateEligibleApproversHandler
 */
final class UpdateEligibleApproversHandlerTest extends BaseKernelTestCase
{
    private UpdateEligibleApproversHandler $handler;
    private ExternalServiceConfigurationRepository $externalServiceConfigurationRepository;

    protected function setUp(): void
    {
        parent::setUp();

        $this->handler = self::getContainer()->get(UpdateEligibleApproversHandler::class);
        $this->externalServiceConfigurationRepository = self::getContainer()->get(ExternalServiceConfigurationRepository::class);
        $this->load([new ExternalServiceConfigurationFixture()]);
    }

    public function testUpdatingEliibleApproversIsSuccessful(): void
    {
        $externalServiceConfiguration = $this->externalServiceConfigurationRepository->getByName('querify_postgres');
        $eligibleApproversBeforeUpdate = $externalServiceConfiguration->eligibleApprovers;

        $command = new UpdateEligibleApprovers(
            'querify_postgres',
            []
        );

        $this->handler->__invoke($command);

        $updatedExternalServiceConfiguration = $this->externalServiceConfigurationRepository->getByName('querify_postgres');

        self::assertCount(0, $updatedExternalServiceConfiguration->eligibleApprovers);
        self::assertNotSame($updatedExternalServiceConfiguration->eligibleApprovers, $eligibleApproversBeforeUpdate);
    }
}
