<?php

declare(strict_types=1);

namespace Querify\Tests\Integration\Application\Command\SubmitDemand;

use Querify\Application\Command\SubmitDemand\SubmitDemand;
use Querify\Application\Command\SubmitDemand\SubmitDemandHandler;
use Querify\Domain\Demand\DemandRepository;
use Querify\Domain\User\Email;
use Querify\Domain\User\Exception\UserNotFoundException;
use Querify\Domain\User\UserRepository;
use Querify\Tests\Fixtures\UserFixture;
use Querify\Tests\integration\BaseKernelTestCase;

/**
 * @internal
 *
 * @coversNothing
 */
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

        $transport = $this->getContainer()->get('messenger.transport.async');
        self::assertCount(1, $transport->getSent());
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
