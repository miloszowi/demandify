<?php

declare(strict_types=1);

namespace Demandify\Domain\User;

use Demandify\Domain\User\Exception\UserNotFoundException;
use Ramsey\Uuid\UuidInterface;

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

    /**
     * @return User[]
     */
    public function getAll(): array;

    /**
     * @param UuidInterface[] $uuids
     *
     * @return User[]
     */
    public function findByUuids(array $uuids): array;
}
