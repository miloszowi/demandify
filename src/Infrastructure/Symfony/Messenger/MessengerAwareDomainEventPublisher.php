<?php

declare(strict_types=1);

namespace Demandify\Infrastructure\Symfony\Messenger;

use Demandify\Domain\DomainEvent;
use Demandify\Domain\DomainEventPublisher;
use Psr\Log\LoggerInterface;
use Symfony\Component\Messenger\MessageBusInterface;

final readonly class MessengerAwareDomainEventPublisher implements DomainEventPublisher
{
    public function __construct(
        private MessageBusInterface $domainEventBus,
        private LoggerInterface $logger
    ) {}

    public function publish(DomainEvent $event): void
    {
        $this->logger->info('Domain event bus dispatching event '.$event::class);
        $this->domainEventBus->dispatch($event);
    }
}
