<?php

declare(strict_types=1);

namespace Demandify\Infrastructure\Authentication\Voter;

use Demandify\Application\Query\IsUserEligibleToDecisionForExternalService\IsUserEligibleToDecisionForExternalService;
use Demandify\Application\Query\QueryBus;
use Demandify\Domain\Demand\Demand;
use Demandify\Domain\User\User;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Authorization\Voter\Voter;

class DemandVoter extends Voter
{
    public const string VIEW = 'view';
    public const string DECISION = 'decision';

    public function __construct(private readonly QueryBus $queryBus) {}

    protected function supports(string $attribute, mixed $subject): bool
    {
        return \in_array($attribute, [self::VIEW, self::DECISION], true) && $subject instanceof Demand;
    }

    protected function voteOnAttribute(string $attribute, mixed $subject, TokenInterface $token): bool
    {
        $user = $token->getUser();

        if (!$user instanceof User) {
            return false;
        }

        /** @var Demand $demand */
        $demand = $subject;

        $isUserEligibleToDecisionForExternalService = $this->queryBus->ask(new IsUserEligibleToDecisionForExternalService($user, $demand->service));

        return match ($attribute) {
            self::VIEW => $demand->requester->uuid->equals($user->uuid)
                || $demand->approver?->uuid->equals($user->uuid)
                || $user->isAdmin()
                || $isUserEligibleToDecisionForExternalService,
            self::DECISION => $isUserEligibleToDecisionForExternalService,
            default => false
        };
    }
}
