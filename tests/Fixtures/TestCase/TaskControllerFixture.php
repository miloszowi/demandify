<?php

declare(strict_types=1);

namespace Demandify\Tests\Fixtures\TestCase;

use Demandify\Domain\Demand\Demand;
use Demandify\Domain\User\Email;
use Demandify\Domain\User\User;
use Demandify\Tests\Doubles\Fakes\FakeDemandExecutor;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;

class TaskControllerFixture extends Fixture
{
    public const string USER_EMAIL = 'user@local.host';

    public function load(ObjectManager $manager): void
    {
        $user = new User(Email::fromString(self::USER_EMAIL));
        $userThatApprovedDemand = new User(Email::fromString('user_that_approved_demand@local.host'));
        $executedDemand = new Demand($user, 'test_service', 'success content', 'First demand reason');
        $executedDemand->approveBy($userThatApprovedDemand);
        $executedDemand->start();
        $executedDemand->execute(new FakeDemandExecutor());

        $failedDemand = new Demand($user, 'test_service', 'failed content', 'First demand reason');
        $failedDemand->approveBy($userThatApprovedDemand);
        $failedDemand->start();
        $failedDemand->execute(new FakeDemandExecutor());

        $manager->persist($user);
        $manager->persist($userThatApprovedDemand);
        $manager->persist($executedDemand);
        $manager->persist($failedDemand);
        $manager->flush();
    }
}
