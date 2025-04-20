<?php

declare(strict_types=1);

namespace Demandify\Tests\Integration\Application\Command\UpdateSentNotificationsWithDecision;

use Demandify\Application\Command\UpdateSentNotificationsWithDecision\UpdateSentNotificationsWithDecision;
use Demandify\Application\Command\UpdateSentNotificationsWithDecision\UpdateSentNotificationsWithDecisionHandler;
use Demandify\Domain\Demand\DemandRepository;
use Demandify\Domain\Demand\Status;
use Demandify\Tests\Fixtures\NotificationFixture;
use Demandify\Tests\Integration\BaseKernelTestCase;
use PHPUnit\Framework\Attributes\CoversClass;

/**
 * @internal
 */
#[CoversClass(UpdateSentNotificationsWithDecisionHandler::class)]
final class UpdateSentNotificationsWithDecisionHandlerTest extends BaseKernelTestCase
{
    private UpdateSentNotificationsWithDecisionHandler $handler;
    private DemandRepository $demandRepository;

    protected function setUp(): void
    {
        parent::setUp();

        $this->handler = self::getContainer()->get(UpdateSentNotificationsWithDecisionHandler::class);
        $this->demandRepository = self::getContainer()->get(DemandRepository::class);

        $this->load([new NotificationFixture()]);
    }

    public function testUpdatingNotificationsWillBeSuccessful(): void
    {
        $command = new UpdateSentNotificationsWithDecision(
            $this->demandRepository->findInStatus(Status::APPROVED)[0]
        );

        $this->handler->__invoke($command);

        self::assertCount(1, $this->getTransport('notification')->getSent());
    }
}
