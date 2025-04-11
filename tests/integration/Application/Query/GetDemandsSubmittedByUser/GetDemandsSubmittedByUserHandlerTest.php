<?php

declare(strict_types=1);

namespace Demandify\Tests\Integration\Application\Query\GetDemandsSubmittedByUser;

use Demandify\Application\Query\GetDemandsSubmittedByUser\GetDemandsSubmittedByUser;
use Demandify\Application\Query\GetDemandsSubmittedByUser\GetDemandsSubmittedByUserHandler;
use Demandify\Domain\User\Email;
use Demandify\Domain\User\UserRepository;
use Demandify\Tests\Fixtures\Demand\ManyDemandsFixture;
use Demandify\Tests\Fixtures\UserFixture;
use Demandify\Tests\Integration\BaseKernelTestCase;
use PHPUnit\Framework\Attributes\CoversClass;

/**
 * @internal
 */
#[CoversClass(GetDemandsSubmittedByUserHandler::class)]
final class GetDemandsSubmittedByUserHandlerTest extends BaseKernelTestCase
{
    private GetDemandsSubmittedByUserHandler $handler;
    private UserRepository $userRepository;

    protected function setUp(): void
    {
        parent::setUp();

        $this->handler = self::getContainer()->get(GetDemandsSubmittedByUserHandler::class);
        $this->userRepository = self::getContainer()->get(UserRepository::class);

        $this->load([new ManyDemandsFixture()]);
    }

    public function testItReturnsDemandsSubmittedByUser(): void
    {
        $userWithTenDemands = $this->userRepository->getByEmail(
            Email::fromString(UserFixture::USER_EMAIL_FIXTURE)
        );

        $query = new GetDemandsSubmittedByUser(
            $userWithTenDemands->uuid,
            page: 1,
            limit: 20,
        );

        $result = $this->handler->__invoke($query);

        foreach ($result->demands as $demand) {
            self::assertStringContainsString((string) $userWithTenDemands->email, $demand['content']);
        }

        self::assertCount(10, $result->demands);
        self::assertSame(10, $result->total);
        self::assertSame(1, $result->page);
        self::assertSame(20, $result->limit);
        self::assertSame(1, $result->totalPages);
        self::assertNull($result->search);

        $userWithFiveDemands = $this->userRepository->getByEmail(
            Email::fromString(UserFixture::USER_WITH_SLACK_SOCIAL_ACCOUNT)
        );

        $query = new GetDemandsSubmittedByUser(
            $userWithFiveDemands->uuid,
            page: 1,
            limit: 20,
        );

        $result = $this->handler->__invoke($query);
        self::assertCount(5, $result->demands);
        self::assertSame(5, $result->total);
        self::assertSame(1, $result->page);
        self::assertSame(20, $result->limit);
        self::assertSame(1, $result->totalPages);
        self::assertNull($result->search);
    }

    public function testItPaginatesDemands(): void
    {
        $userWithTenDemands = $this->userRepository->getByEmail(
            Email::fromString(UserFixture::USER_EMAIL_FIXTURE)
        );

        $query = new GetDemandsSubmittedByUser(
            $userWithTenDemands->uuid,
            page: 1,
            limit: 2,
        );

        $result = $this->handler->__invoke($query);
        self::assertCount(2, $result->demands);
        self::assertSame(10, $result->total);
        self::assertSame(1, $result->page);
        self::assertSame(2, $result->limit);
        self::assertSame(5, $result->totalPages);
        self::assertNull($result->search);
    }

    public function testItSearchesDemands(): void
    {
        $userWithTenDemands = $this->userRepository->getByEmail(
            Email::fromString(UserFixture::USER_EMAIL_FIXTURE)
        );

        $query = new GetDemandsSubmittedByUser(
            $userWithTenDemands->uuid,
            page: 1,
            limit: 20,
            search: ManyDemandsFixture::HIDDEN_SEARCH_TERM,
        );

        $result = $this->handler->__invoke($query);

        self::assertCount(7, $result->demands);
        self::assertSame(7, $result->total);
        self::assertSame(1, $result->page);
        self::assertSame(20, $result->limit);
        self::assertSame(1, $result->totalPages);
        self::assertSame(ManyDemandsFixture::HIDDEN_SEARCH_TERM, $result->search);
    }

    public function testItReturnsEmptyResultWhenNoDemandsFound(): void
    {
        $userWithNoDemands = $this->userRepository->getByEmail(
            Email::fromString(UserFixture::USER_NOT_ELIGIBLE_TO_APPROVE)
        );

        $query = new GetDemandsSubmittedByUser(
            $userWithNoDemands->uuid,
            page: 1,
            limit: 20,
        );

        $result = $this->handler->__invoke($query);
        self::assertCount(0, $result->demands);
        self::assertSame(0, $result->total);
        self::assertSame(1, $result->page);
        self::assertSame(20, $result->limit);
        self::assertSame(0, $result->totalPages);
        self::assertNull($result->search);
    }
}
