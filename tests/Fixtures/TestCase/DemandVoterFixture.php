<?php

declare(strict_types=1);

namespace Demandify\Tests\Fixtures\TestCase;

use Demandify\Domain\Demand\Demand;
use Demandify\Domain\ExternalService\ExternalServiceConfiguration;
use Demandify\Domain\User\Email;
use Demandify\Domain\User\User;
use Demandify\Domain\User\UserRole;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;

class DemandVoterFixture extends Fixture
{
    public const string USER_EMAIL_THAT_SUBMITTED_DEMAND = 'user_that_submitted_demand@local.host';
    public const string USER_EMAIL_THAT_IS_ELIGIBLE_TO_DECISION = 'user_that_is_eligible_to_decision@local.host';
    public const string ADMIN_USER_EMAIL = 'admin@local.host';
    public const string USER_EMAIL_THAT_DID_NOT_SUBMIT_NOR_APPROVE = 'user_that_did_not_submit_nor_approve@local.host';

    public function load(ObjectManager $manager): void
    {
        $userThatSubmittedDemand = new User(
            Email::fromString(self::USER_EMAIL_THAT_SUBMITTED_DEMAND)
        );
        $userThatIsEligibleToDecision = new User(
            Email::fromString(self::USER_EMAIL_THAT_IS_ELIGIBLE_TO_DECISION)
        );
        $adminUser = new User(
            Email::fromString(self::ADMIN_USER_EMAIL)
        );
        $adminUser->grantPrivilege(UserRole::ROLE_ADMIN);
        $userThatDidNotSubmitNorApprove = new User(
            Email::fromString(self::USER_EMAIL_THAT_DID_NOT_SUBMIT_NOR_APPROVE)
        );

        $demand = new Demand($userThatSubmittedDemand, 'demandify_postgres', 'test content', 'test reason');

        $externalServiceConfiguration = new ExternalServiceConfiguration(
            'demandify_postgres',
            [$userThatIsEligibleToDecision->uuid->toString()]
        );

        $manager->persist($userThatSubmittedDemand);
        $manager->persist($userThatIsEligibleToDecision);
        $manager->persist($adminUser);
        $manager->persist($userThatDidNotSubmitNorApprove);
        $manager->persist($demand);
        $manager->persist($externalServiceConfiguration);
        $manager->flush();
    }
}
