<?php

declare(strict_types=1);

namespace Demandify\Infrastructure\Symfony\Messenger;

use Demandify\Application\Command\Command;
use Demandify\Application\Command\CommandBus as CommandBusInterface;
use Psr\Log\LoggerInterface;
use Symfony\Component\Messenger\MessageBusInterface;

final class CommandBus implements CommandBusInterface
{
    public function __construct(
        private readonly MessageBusInterface $commandBus,
        private readonly LoggerInterface $logger
    ) {}

    public function dispatch(Command $command): void
    {
        $this->logger->info('Command bus dispatching command'.$command::class);
        $this->commandBus->dispatch($command);
    }
}
