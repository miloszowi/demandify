<?php

declare(strict_types=1);

namespace Demandify\Tests\Integration\Application\Event\TaskSucceeded;

use Demandify\Application\Event\TaskSucceeded\TaskSucceededHandler;
use Demandify\Domain\Demand\DemandRepository;
use Demandify\Domain\Demand\Status;
use Demandify\Domain\Task\Event\TaskSucceeded;
use Demandify\Tests\Fixtures\Demand\SuccessfulDemandFixture;
use Demandify\Tests\Integration\BaseKernelTestCase;
use PHPUnit\Framework\Attributes\CoversClass;

/**
 * @internal
 */
#[CoversClass(TaskSucceededHandler::class)]
final class TaskSucceededHandlerTest extends BaseKernelTestCase
{
    private TaskSucceededHandler $handler;
    private DemandRepository $demandRepository;

    protected function setUp(): void
    {
        parent::setUp();

        $this->handler = self::getContainer()->get(TaskSucceededHandler::class);
        $this->demandRepository = self::getContainer()->get(DemandRepository::class);

        $this->load([new SuccessfulDemandFixture()]);
    }

    public function testItSendsNotificationToRequester(): void
    {
        $demand = $this->demandRepository->findInStatus(Status::EXECUTED)[0];

        $event = new TaskSucceeded($demand->uuid);

        $this->handler->__invoke($event);

        self::assertCount(1, $this->getAsyncTransport()->getSent());
    }
}
