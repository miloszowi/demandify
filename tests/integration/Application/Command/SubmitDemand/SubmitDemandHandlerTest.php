<?php

declare(strict_types=1);

namespace Demandify\Tests\Integration\Application\Command\SubmitDemand;

use Demandify\Application\Command\SubmitDemand\SubmitDemand;
use Demandify\Application\Command\SubmitDemand\SubmitDemandHandler;
use Demandify\Domain\Demand\DemandRepository;
use Demandify\Domain\User\Email;
use Demandify\Domain\User\Exception\UserNotFoundException;
use Demandify\Domain\User\UserRepository;
use Demandify\Tests\Fixtures\UserFixture;
use Demandify\Tests\Integration\BaseKernelTestCase;
use PHPUnit\Framework\Attributes\CoversClass;

/**
 * @internal
 */
#[CoversClass(SubmitDemandHandler::class)]
final class SubmitDemandHandlerTest extends BaseKernelTestCase
{
    private DemandRepository $demandRepository;
    private UserRepository $userRepository;
    private SubmitDemandHandler $handler;

    protected function setUp(): void
    {
        parent::setUp();

        $this->demandRepository = self::getContainer()->get(DemandRepository::class);
        $this->userRepository = self::getContainer()->get(UserRepository::class);
        $this->handler = self::getContainer()->get(SubmitDemandHandler::class);

        $this->load([new UserFixture()]);
    }

    public function testSubmittingDemandSuccessfully(): void
    {
        $command = new SubmitDemand(
            UserFixture::USER_EMAIL_FIXTURE,
            'service',
            'content',
            'reason'
        );

        $this->handler->__invoke($command);

        $demands = $this->demandRepository->findAllFromUser(
            $this->userRepository->getByEmail(Email::fromString(UserFixture::USER_EMAIL_FIXTURE))
        );

        self::assertSame('service', $demands[0]->service);
        self::assertSame('content', $demands[0]->content);
        self::assertSame('reason', $demands[0]->reason);

        self::assertCount(1, $this->getAsyncTransport()->getSent());
    }

    public function testSubmittingDemandWillFailDueToNonExistingUser(): void
    {
        $command = new SubmitDemand(
            'non-existing@local.host',
            'service',
            'content',
            'reason'
        );

        $this->expectException(UserNotFoundException::class);

        $this->handler->__invoke($command);

        $transport = $this->getContainer()->get('messenger.transport.async');
        self::assertCount(0, $transport->getSent());
    }
}
