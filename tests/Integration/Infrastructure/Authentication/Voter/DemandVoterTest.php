<?php

declare(strict_types=1);

namespace Demandify\Tests\Integration\Infrastructure\Authentication\Voter;

use Demandify\Domain\Demand\Demand;
use Demandify\Domain\Demand\DemandRepository;
use Demandify\Domain\Demand\Status;
use Demandify\Domain\User\Email;
use Demandify\Infrastructure\Authentication\Voter\DemandVoter;
use Demandify\Tests\Fixtures\TestCase;
use Demandify\Tests\Integration\BaseKernelTestCase;
use PHPUnit\Framework\Attributes\CoversClass;
use Symfony\Component\Security\Core\Authorization\Voter\VoterInterface;

/**
 * @internal
 */
#[CoversClass(DemandVoter::class)]
final class DemandVoterTest extends BaseKernelTestCase
{
    private DemandVoter $voter;
    private Demand $demand;

    protected function setUp(): void
    {
        parent::setUp();

        $this->voter = self::getContainer()->get(DemandVoter::class);

        $this->load([
            new TestCase\DemandVoterFixture(),
        ]);

        $this->demand = self::getContainer()->get(DemandRepository::class)->findInStatus(Status::NEW)[0];
    }

    /**
     * @dataProvider provideVoteCases
     */
    public function testVote(int $expectedResult, string $attribute, string $userEmail): void
    {
        $token = $this->getTokenByUserEmail(Email::fromString($userEmail));

        self::assertSame($expectedResult, $this->voter->vote($token, $this->demand, [$attribute]));
    }

    /**
     * @return array<int, array<int, int|string>>
     */
    public static function provideVoteCases(): iterable
    {
        return [
            [VoterInterface::ACCESS_GRANTED, DemandVoter::VIEW, TestCase\DemandVoterFixture::USER_EMAIL_THAT_SUBMITTED_DEMAND],
            [VoterInterface::ACCESS_DENIED, DemandVoter::DECISION, TestCase\DemandVoterFixture::USER_EMAIL_THAT_SUBMITTED_DEMAND],
            [VoterInterface::ACCESS_GRANTED, DemandVoter::VIEW, TestCase\DemandVoterFixture::USER_EMAIL_THAT_IS_ELIGIBLE_TO_DECISION],
            [VoterInterface::ACCESS_GRANTED, DemandVoter::DECISION, TestCase\DemandVoterFixture::USER_EMAIL_THAT_IS_ELIGIBLE_TO_DECISION],
            [VoterInterface::ACCESS_DENIED, DemandVoter::VIEW, TestCase\DemandVoterFixture::USER_EMAIL_THAT_DID_NOT_SUBMIT_NOR_APPROVE],
            [VoterInterface::ACCESS_DENIED, DemandVoter::DECISION, TestCase\DemandVoterFixture::USER_EMAIL_THAT_DID_NOT_SUBMIT_NOR_APPROVE],
            [VoterInterface::ACCESS_GRANTED, DemandVoter::VIEW, TestCase\DemandVoterFixture::ADMIN_USER_EMAIL],
            [VoterInterface::ACCESS_GRANTED, DemandVoter::VIEW, TestCase\DemandVoterFixture::ADMIN_USER_EMAIL],
        ];
    }

    public function testVoteForNullUser(): void
    {
        $token = $this->getNullToken();

        self::assertSame(VoterInterface::ACCESS_DENIED, $this->voter->vote($token, $this->demand, [DemandVoter::VIEW]));
    }
}
