<?php

declare(strict_types=1);

namespace Querify\Domain\User;

use Querify\Domain\User\Exception\UserNotFoundException;
use Ramsey\Uuid\UuidInterface;
use Symfony\Bridge\Doctrine\Security\User\UserLoaderInterface;

interface UserRepository
{
    /**
     * @throws UserNotFoundException
     */
    public function getByUuid(UuidInterface $uuid): User;

    public function findByEmail(Email $email): ?User;

    /**
     * @throws UserNotFoundException
     */
    public function getByEmail(Email $email): User;

    public function save(User $user): void;
}
