<?php

declare(strict_types=1);

namespace Querify\Application\Query\GetUser;

use Querify\Domain\User\Email;
use Querify\Domain\User\User;
use Querify\Domain\User\UserRepository;
use Symfony\Component\Messenger\Attribute\AsMessageHandler;

#[AsMessageHandler]
class GetUserByEmailHander
{
    public function __construct(private readonly UserRepository $userRepository) {}

    public function __invoke(GetUserByEmail $query): User
    {
        return $this->userRepository->getByEmail(Email::fromString($query->email));
    }
}
