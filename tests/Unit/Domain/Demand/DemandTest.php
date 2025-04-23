<?php

declare(strict_types=1);

namespace Demandify\Tests\Unit\Domain\Demand;

use Demandify\Domain\Demand\Demand;
use Demandify\Domain\Demand\Exception\InvalidDemandStatusException;
use Demandify\Domain\Demand\Status;
use Demandify\Domain\Task\Task;
use Demandify\Domain\User\User;
use Demandify\Tests\Doubles\Fakes\FakeDemandExecutor;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;
use Ramsey\Uuid\UuidInterface;

/**
 * @internal
 */
#[CoversClass(Demand::class)]
final class DemandTest extends TestCase
{
    private User $requester;
    private Demand $demand;

    protected function setUp(): void
    {
        $this->requester = $this->createMock(User::class);
        $this->demand = new Demand(
            $this->requester,
            'Sample Service',
            'This is a test content',
            'This is a test reason'
        );
    }

    public function testInitializable(): void
    {
        self::assertInstanceOf(Demand::class, $this->demand);
        self::assertCount(1, $this->demand->releaseEvents());
    }

    public function testHasCorrectData(): void
    {
        self::assertInstanceOf(UuidInterface::class, $this->demand->uuid);
        self::assertSame(Status::NEW, $this->demand->status);
        self::assertSame($this->requester, $this->demand->requester);
        self::assertSame('Sample Service', $this->demand->service);
        self::assertSame('This is a test content', $this->demand->content);
        self::assertSame('This is a test reason', $this->demand->reason);
        self::assertNull($this->demand->approver);
    }

    public function testCanBeApproved(): void
    {
        $demand = new Demand($this->requester, 'service', 'content', 'reason');
        $demand->releaseEvents();
        $approver = $this->createMock(User::class);

        $demand->approveBy($approver);

        self::assertSame(Status::APPROVED, $demand->status);
        self::assertSame($approver, $demand->approver);
        self::assertInstanceOf(\DateTimeImmutable::class, $demand->updatedAt);
        self::assertCount(1, $demand->releaseEvents());
    }

    public function testCanBeDeclined(): void
    {
        $demand = new Demand($this->requester, 'service', 'content', 'reason');
        $demand->releaseEvents();
        $approver = $this->createMock(User::class);

        $demand->declineBy($approver);

        self::assertSame(Status::DECLINED, $demand->status);
        self::assertSame($approver, $demand->approver);
        self::assertInstanceOf(\DateTimeImmutable::class, $demand->updatedAt);
        self::assertCount(1, $demand->releaseEvents());
    }

    public function testThrowsExceptionIfApproveIsCalledTwice(): void
    {
        $approver = $this->createMock(User::class);

        $this->demand->approveBy($approver);

        $this->expectException(InvalidDemandStatusException::class);
        $this->demand->approveBy($approver);
    }

    public function testThrowsExceptionIfDeclineIsCalledTwice(): void
    {
        $approver = $this->createMock(User::class);

        $this->demand->declineBy($approver);

        $this->expectException(InvalidDemandStatusException::class);
        $this->demand->declineBy($approver);
    }

    public function testThrowsExceptionIfDeclineIsCalledAfterApproval(): void
    {
        $approver = $this->createMock(User::class);

        $this->demand->approveBy($approver);

        $this->expectException(InvalidDemandStatusException::class);
        $this->demand->declineBy($approver);
    }

    public function testCanBeStarted(): void
    {
        $approvedDemand = new Demand(
            $this->createMock(User::class),
            'Sample Service',
            'This is a test content',
            'This is a test reason'
        );
        $approvedDemand->approveBy($this->createMock(User::class));
        $approvedDemand->start();

        self::assertSame(Status::IN_PROGRESS, $approvedDemand->status);
    }

    public function testCanNotBeStartedIfNotApproved(): void
    {
        $this->expectException(InvalidDemandStatusException::class);
        $this->demand->start();
    }

    public function testCanBeExecuted(): void
    {
        $demand = new Demand(
            $this->createMock(User::class),
            'Sample Service',
            'This is a success content',
            'This is a success reason'
        );

        $demand->approveBy($this->createMock(User::class));
        $demand->start();
        $demand->releaseEvents();
        $demand->execute(new FakeDemandExecutor());

        self::assertSame(Status::EXECUTED, $demand->status);
        $fakeTaskResult = FakeDemandExecutor::getSuccessResult();

        self::assertInstanceOf(Task::class, $demand->task);
        self::assertSame($fakeTaskResult->success, $demand->task->success);
        self::assertSame(
            $fakeTaskResult->executedAt->format('Y-m-d H:i:s'),
            $demand->task->executedAt->format('Y-m-d H:i:s')
        );
        self::assertSame($fakeTaskResult->resultPath, $demand->task->resultPath);
        self::assertSame($fakeTaskResult->executionTime, $demand->task->executionTime);
        self::assertSame($fakeTaskResult->errorMessage, $demand->task->errorMessage);
        self::assertCount(1, $demand->releaseEvents());
    }

    public function testCanBeExecutedAndFail(): void
    {
        $demand = new Demand(
            $this->createMock(User::class),
            'Sample Service',
            'This is a failed content',
            'This is a failed reason'
        );

        $demand->approveBy($this->createMock(User::class));
        $demand->releaseEvents();
        $demand->start();
        $demand->execute(new FakeDemandExecutor());

        self::assertSame(Status::FAILED, $demand->status);
        $fakeTaskResult = FakeDemandExecutor::getFailedResult();

        self::assertInstanceOf(Task::class, $demand->task);
        self::assertSame($fakeTaskResult->success, $demand->task->success);
        self::assertSame(
            $fakeTaskResult->executedAt->format('Y-m-d H:i:s'),
            $demand->task->executedAt->format('Y-m-d H:i:s')
        );
        self::assertSame($fakeTaskResult->resultPath, $demand->task->resultPath);
        self::assertSame($fakeTaskResult->executionTime, $demand->task->executionTime);
        self::assertSame($fakeTaskResult->errorMessage, $demand->task->errorMessage);
        self::assertCount(1, $demand->releaseEvents());
    }
}
