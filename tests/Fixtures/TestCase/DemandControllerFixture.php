<?php

declare(strict_types=1);

namespace Demandify\Tests\Fixtures\TestCase;

use Demandify\Domain\Demand\Demand;
use Demandify\Domain\ExternalService\ExternalServiceConfiguration;
use Demandify\Domain\User\Email;
use Demandify\Domain\User\User;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;

class DemandControllerFixture extends Fixture
{
    public const USER_EMAIL = 'user@local.host';

    public function load(ObjectManager $manager): void
    {
        $user = new User(Email::fromString(self::USER_EMAIL));
        $userThatSubmittedDemand = new User(Email::fromString('user_that_submitted_demand@local.host'));

        $firstDemandCreatedByUser = new Demand($user, 'test_service', 'First demand content', 'First demand reason');
        $secondDemandCreatedByUser = new Demand($user, 'test_service', 'Second demand content', 'Second demand reason');
        $thirdDemandCreatedByUser = new Demand($user, 'test_service', 'Third demand content', 'Third demand reason');

        $firstDemandToDecisionByUser = new Demand($userThatSubmittedDemand, 'demandify_postgres', 'Demand to be approved content', 'Demand to be approved reason');
        $secondDemandToDecisionByUser = new Demand($userThatSubmittedDemand, 'demandify_postgres', 'Demand to be approved content', 'Demand to be approved reason');

        $externalServiceConfiguration = new ExternalServiceConfiguration(
            'demandify_postgres',
            [$user->uuid->toString()]
        );

        $manager->persist($user);
        $manager->persist($userThatSubmittedDemand);
        $manager->persist($firstDemandCreatedByUser);
        $manager->persist($secondDemandCreatedByUser);
        $manager->persist($thirdDemandCreatedByUser);
        $manager->persist($firstDemandToDecisionByUser);
        $manager->persist($secondDemandToDecisionByUser);
        $manager->persist($externalServiceConfiguration);
        $manager->flush();
    }
}
