<?php

declare(strict_types=1);

namespace Demandify\Infrastructure\Symfony\Messenger;

use Demandify\Domain\DomainEvent;
use Demandify\Domain\DomainEventPublisher;
use Symfony\Component\Messenger\MessageBusInterface;

final readonly class MessengerAwareDomainEventPublisher implements DomainEventPublisher
{
    public function __construct(private MessageBusInterface $messageBus) {}

    public function publish(DomainEvent $event): void
    {
        $this->messageBus->dispatch($event);
    }
}
