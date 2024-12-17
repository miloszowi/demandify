<?php

namespace spec\Querify\Domain\Demand;

use PhpSpec\ObjectBehavior;
use Querify\Domain\Demand\Demand;
use Querify\Domain\Demand\Exception\InvalidDemandStatusException;
use Querify\Domain\Demand\Status;
use Querify\Domain\User\User;
use Ramsey\Uuid\UuidInterface;

class DemandSpec extends ObjectBehavior
{
    public function let(User $requester): void
    {
        $this->beConstructedWith(
            $requester,
            'Sample Service',
            'This is a test content',
            'This is a test reason'
        );
    }

    public function it_is_initializable(): void
    {
        $this->shouldHaveType(Demand::class);
    }

    public function it_has_correct_data(User $requester): void
    {
        $this->uuid->shouldBeAnInstanceOf(UuidInterface::class);
        $this->status->shouldBe(Status::NEW);
        $this->requester->shouldBe($requester);
        $this->service->shouldBe('Sample Service');
        $this->content->shouldBe('This is a test content');
        $this->reason->shouldBe('This is a test reason');
        $this->approver->shouldBe(null);
    }

    public function it_can_be_approved_by(User $approver): void
    {
        $this->approveBy($approver);

        $this->status->shouldBe(Status::APPROVED);
        $this->approver->shouldBe($approver);
        $this->updatedAt->shouldBeAnInstanceOf(\DateTimeImmutable::class);
    }

    public function it_can_be_declined_by(User $approver): void
    {
        $this->declineBy($approver);

        $this->status->shouldBe(Status::DECLINED);
        $this->approver->shouldBe($approver);
        $this->updatedAt->shouldBeAnInstanceOf(\DateTimeImmutable::class);
    }

    public function it_throws_exception_if_approve_is_called_twice(User $approver): void
    {
        $this->approveBy($approver);

        $this->shouldThrow(InvalidDemandStatusException::class)
            ->during('approveBy', [$approver])
        ;
    }

    public function it_throws_exception_if_decline_is_called_after_approval(User $approver): void
    {
        $this->approveBy($approver);

        $this->shouldThrow(InvalidDemandStatusException::class)
            ->during('declineBy', [$approver])
        ;
    }
}
