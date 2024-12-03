<?php

declare(strict_types=1);

namespace Querify\Application\Event\UserRegistered;

use Ramsey\Uuid\UuidInterface;
use Symfony\Component\Messenger\Attribute\AsMessage;

#[AsMessage]
final readonly class UserRegistered
{
    public function __construct(public UuidInterface $userId)
    {
    }
}