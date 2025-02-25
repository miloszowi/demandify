<?php

declare(strict_types=1);

namespace Demandify\Application\Query\GetUser;

use Demandify\Domain\User\Email;
use Demandify\Domain\User\User;
use Demandify\Domain\User\UserRepository;
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
