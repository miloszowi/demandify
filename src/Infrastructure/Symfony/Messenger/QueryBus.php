<?php

declare(strict_types=1);

namespace Demandify\Infrastructure\Symfony\Messenger;

use Demandify\Application\Query\Query;
use Demandify\Application\Query\QueryBus as QueryBusInterface;
use Psr\Log\LoggerInterface;
use Symfony\Component\Messenger\HandleTrait;
use Symfony\Component\Messenger\MessageBusInterface;

final class QueryBus implements QueryBusInterface
{
    use HandleTrait;

    public function __construct(
        private readonly LoggerInterface $logger,
        MessageBusInterface $queryBus,
    ) {
        $this->messageBus = $queryBus;
    }

    public function ask(Query $query): mixed
    {
        $this->logger->info('Query bus dispatching query '.$query::class);

        return $this->handle($query);
    }
}
