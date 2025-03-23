<?php

declare(strict_types=1);

namespace Demandify\Application\Query\GetUser;

use Demandify\Application\Query\QueryHandler;
use Demandify\Domain\User\Email;
use Demandify\Domain\User\User;
use Demandify\Domain\User\UserRepository;

class GetUserByEmailHander implements QueryHandler
{
    public function __construct(private readonly UserRepository $userRepository) {}

    public function __invoke(GetUserByEmail $query): User
    {
        return $this->userRepository->getByEmail(Email::fromString($query->email));
    }
}
