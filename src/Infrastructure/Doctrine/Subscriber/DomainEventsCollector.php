<?php

declare(strict_types=1);

namespace Demandify\Infrastructure\Doctrine\Subscriber;

use Demandify\Domain\DomainEventPublisher;
use Demandify\Domain\EventReleasable;
use Doctrine\Bundle\DoctrineBundle\Attribute\AsDoctrineListener;
use Doctrine\ORM\Events;
use Doctrine\Persistence\Event\LifecycleEventArgs;

#[AsDoctrineListener(event: Events::postPersist)]
#[AsDoctrineListener(event: Events::postUpdate)]
#[AsDoctrineListener(event: Events::preRemove)]
#[AsDoctrineListener(event: Events::postRemove)]
readonly class DomainEventsCollector
{
    public function __construct(private DomainEventPublisher $domainEventPublisher) {}

    public function postPersist(LifecycleEventArgs $event): void
    {
        $this->doCollect($event);
    }

    public function postUpdate(LifecycleEventArgs $event): void
    {
        $this->doCollect($event);
    }

    public function preRemove(LifecycleEventArgs $event): void
    {
        $this->doCollect($event);
    }

    public function postRemove(LifecycleEventArgs $event): void
    {
        $this->doCollect($event);
    }

    public function doCollect(LifecycleEventArgs $event): void
    {
        $entity = $event->getObject();

        if (!$entity instanceof EventReleasable) {
            return;
        }

        foreach ($entity->releaseEvents() as $event) {
            $this->domainEventPublisher->publish($event);
        }
    }
}
