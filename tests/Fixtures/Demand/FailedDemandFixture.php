<?php

declare(strict_types=1);

namespace Demandify\Tests\Fixtures\Demand;

use Demandify\Domain\Demand\Demand;
use Demandify\Domain\User\User;
use Demandify\Tests\Doubles\Fakes\FakeDemandExecutor;
use Demandify\Tests\Fixtures\UserFixture;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Common\DataFixtures\DependentFixtureInterface;
use Doctrine\Persistence\ObjectManager;

class FailedDemandFixture extends Fixture implements DependentFixtureInterface
{
    public function load(ObjectManager $manager): void
    {
        $user = $this->getReference(UserFixture::USER_EMAIL_FIXTURE, User::class);

        $successfulDemand = new Demand(
            $user,
            'demandify_postgres',
            'test failed content',
            'test failed reason'
        );
        $successfulDemand->approveBy($user);
        $successfulDemand->start();
        $successfulDemand->execute(new FakeDemandExecutor());

        $manager->persist($successfulDemand);
        $manager->flush();
    }

    public function getDependencies(): array
    {
        return [
            UserFixture::class,
        ];
    }
}
