<?php

declare(strict_types=1);

namespace Demandify\Tests\Unit\Domain\Demand;

use Demandify\Domain\Demand\Demand;
use Demandify\Domain\Demand\Exception\InvalidDemandStatusException;
use Demandify\Domain\Demand\Status;
use Demandify\Domain\User\User;
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

    public function testCanBeApprovedBy(): void
    {
        $approver = $this->createMock(User::class);

        $this->demand->approveBy($approver);

        self::assertSame(Status::APPROVED, $this->demand->status);
        self::assertSame($approver, $this->demand->approver);
        self::assertInstanceOf(\DateTimeImmutable::class, $this->demand->updatedAt);
    }

    public function testCanBeDeclinedBy(): void
    {
        $approver = $this->createMock(User::class);

        $this->demand->declineBy($approver);

        self::assertSame(Status::DECLINED, $this->demand->status);
        self::assertSame($approver, $this->demand->approver);
        self::assertInstanceOf(\DateTimeImmutable::class, $this->demand->updatedAt);
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
}
