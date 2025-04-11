<?php

declare(strict_types=1);

namespace Demandify\Tests\Unit\Application\Query\GetDemandsSubmittedByUser;

use Demandify\Application\Query\GetDemandsSubmittedByUser\GetDemandsSubmittedByUser;
use Demandify\Application\Query\GetDemandsSubmittedByUser\GetDemandsSubmittedByUserHandler;
use Demandify\Application\Query\ReadModel\DemandsSubmittedByUser;
use Demandify\Domain\Demand\DemandRepository;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Ramsey\Uuid\Uuid;

/**
 * @internal
 */
#[CoversClass(GetDemandsSubmittedByUserHandler::class)]
final class GetDemandsSubmittedByUserHandlerTest extends TestCase
{
    private GetDemandsSubmittedByUserHandler $handler;
    private DemandRepository|MockObject $demandRepository;

    protected function setUp(): void
    {
        $this->demandRepository = $this->createMock(DemandRepository::class);
        $this->handler = new GetDemandsSubmittedByUserHandler($this->demandRepository);
    }

    public function testItReturnsAllDemandsSubmittedByUser(): void
    {
        $this->demandRepository
            ->expects(self::once())
            ->method('findPaginatedForUser')
            ->willReturn(
                [
                    'demands' => [$this->createMock(\stdClass::class)],
                    'total' => 100,
                    'page' => 1,
                    'limit' => 20,
                    'totalPages' => 5,
                    'search' => null,
                ]
            )
        ;

        $query = new GetDemandsSubmittedByUser(
            Uuid::uuid4(),
            page: 1,
            limit: 20,
        );

        $result = $this->handler->__invoke($query);

        self::assertInstanceOf(DemandsSubmittedByUser::class, $result);
        self::assertCount(1, $result->demands);
        self::assertSame(100, $result->total);
        self::assertSame(1, $result->page);
        self::assertSame(20, $result->limit);
        self::assertSame(5, $result->totalPages);
        self::assertNull($result->search);
    }
}
