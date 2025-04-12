<?php

declare(strict_types=1);

namespace Demandify\Tests\Integration\Application\Event\TaskFailed;

use Demandify\Application\Command\SendDemandNotification\SendDemandNotification;
use Demandify\Application\Event\TaskFailed\TaskFailedHandler;
use Demandify\Domain\Demand\DemandRepository;
use Demandify\Domain\Demand\Status;
use Demandify\Domain\Task\Event\TaskFailed;
use Demandify\Tests\Fixtures\Demand\FailedDemandFixture;
use Demandify\Tests\Integration\BaseKernelTestCase;
use PHPUnit\Framework\Attributes\CoversClass;

/**
 * @internal
 */
#[CoversClass(TaskFailedHandler::class)]
final class TaskFailedHandlerTest extends BaseKernelTestCase
{
    private TaskFailedHandler $handler;
    private DemandRepository $demandRepository;

    protected function setUp(): void
    {
        parent::setUp();

        $this->handler = self::getContainer()->get(TaskFailedHandler::class);
        $this->demandRepository = self::getContainer()->get(DemandRepository::class);

        $this->load([new FailedDemandFixture()]);
    }

    public function testItSendsNotificationToRequester(): void
    {
        $demand = $this->demandRepository->findInStatus(Status::FAILED)[0];

        $event = new TaskFailed($demand);

        $this->handler->__invoke($event);

        $sentMessages = $this->getAsyncTransport()->getSent();
        self::assertCount(1, $sentMessages);
        $sendNotification = $sentMessages[0]->getMessage();
        self::assertInstanceOf(SendDemandNotification::class, $sendNotification);
        self::assertSame($demand, $sendNotification->demand);
    }
}
