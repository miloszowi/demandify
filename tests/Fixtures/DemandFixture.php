<?php

declare(strict_types=1);

namespace Querify\Tests\Fixtures;

use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Common\DataFixtures\DependentFixtureInterface;
use Doctrine\Persistence\ObjectManager;
use Querify\Domain\Demand\Demand;
use Querify\Domain\User\User;

class DemandFixture extends Fixture implements DependentFixtureInterface
{
    public const string APPROVED_DEMAND_FIXTURE_KEY = 'approved_demand';
    public const string DECLINED_DEMAND_FIXTURE_KEY = 'declined_demand';

    public function load(ObjectManager $manager): void
    {
        $user = $this->getReference(UserFixture::USER_EMAIL_FIXTURE, User::class);
        $demand = new Demand(
            $user,
            'querify_postgres',
            'test content',
            'test reason'
        );

        $approvedDemand = new Demand(
            $user,
            'querify_postgres',
            'test approved content',
            'test approved reason'
        );
        $approvedDemand->approveBy($user);

        $declinedDemand = new Demand(
            $user,
            'querify_postgres',
            'test declined content',
            'test declined reason'
        );
        $declinedDemand->declineBy($user);

        $manager->persist($demand);
        $manager->persist($approvedDemand);
        $this->setReference(self::APPROVED_DEMAND_FIXTURE_KEY, $approvedDemand);
        $manager->persist($declinedDemand);
        $this->setReference(self::DECLINED_DEMAND_FIXTURE_KEY, $approvedDemand);
        $manager->flush();
    }

    public function getDependencies(): array
    {
        return [
            UserFixture::class,
        ];
    }
}
