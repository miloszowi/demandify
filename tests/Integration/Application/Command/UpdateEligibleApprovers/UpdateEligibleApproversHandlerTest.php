<?php

declare(strict_types=1);

namespace Demandify\Tests\Integration\Application\Command\UpdateEligibleApprovers;

use Demandify\Application\Command\UpdateEligibleApprovers\UpdateEligibleApprovers;
use Demandify\Application\Command\UpdateEligibleApprovers\UpdateEligibleApproversHandler;
use Demandify\Domain\ExternalService\ExternalServiceConfigurationRepository;
use Demandify\Tests\Fixtures\ExternalServiceConfigurationFixture;
use Demandify\Tests\Integration\BaseKernelTestCase;
use PHPUnit\Framework\Attributes\CoversClass;

/**
 * @internal
 */
#[CoversClass(UpdateEligibleApproversHandler::class)]
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
        $externalServiceConfiguration = $this->externalServiceConfigurationRepository->getByName('demandify_postgres');
        $eligibleApproversBeforeUpdate = $externalServiceConfiguration->eligibleApprovers;

        $command = new UpdateEligibleApprovers(
            'demandify_postgres',
            []
        );

        $this->handler->__invoke($command);

        $updatedExternalServiceConfiguration = $this->externalServiceConfigurationRepository->getByName('demandify_postgres');

        self::assertCount(0, $updatedExternalServiceConfiguration->eligibleApprovers);
        self::assertNotSame($updatedExternalServiceConfiguration->eligibleApprovers, $eligibleApproversBeforeUpdate);
    }
}
