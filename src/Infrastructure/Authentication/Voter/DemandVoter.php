<?php

declare(strict_types=1);

namespace Demandify\Infrastructure\Authentication\Voter;

use Demandify\Domain\Demand\Demand;
use Demandify\Domain\User\User;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Authorization\Voter\Voter;

class DemandVoter extends Voter
{
    public const VIEW = 'view';

    protected function supports(string $attribute, mixed $subject): bool
    {
        return self::VIEW === $attribute && $subject instanceof Demand;
    }

    protected function voteOnAttribute(string $attribute, mixed $subject, TokenInterface $token): bool
    {
        $user = $token->getUser();

        if (!$user instanceof User) {
            return false;
        }

        /** @var Demand $demand */
        $demand = $subject;

        return $demand->requester->uuid->equals($user->uuid) || $demand->approver?->uuid->equals($user->uuid);
    }
}
