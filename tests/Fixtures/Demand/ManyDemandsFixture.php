<?php

declare(strict_types=1);

namespace Demandify\Tests\Fixtures\Demand;

use Demandify\Domain\Demand\Demand;
use Demandify\Domain\User\User;
use Demandify\Tests\Fixtures\UserFixture;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Common\DataFixtures\DependentFixtureInterface;
use Doctrine\Persistence\ObjectManager;

class ManyDemandsFixture extends Fixture implements DependentFixtureInterface
{
    public const string HIDDEN_SEARCH_TERM = 'hiddenSearchTerm';

    public function load(ObjectManager $manager): void
    {
        $userWithTenDemands = $this->getReference(UserFixture::USER_EMAIL_FIXTURE, User::class);

        for ($i = 0; $i < 10; ++$i) {
            $content = $i < 7
                ? \sprintf('[%s] demand content %d from user %s', self::HIDDEN_SEARCH_TERM, $i, $userWithTenDemands->email)
                : \sprintf('demand content %d from user %s', $i, $userWithTenDemands->email);

            $demand = new Demand(
                $userWithTenDemands,
                'demandify_postgres',
                $content,
                \sprintf('demand reason %d from user %s', $i, $userWithTenDemands->email)
            );
            $manager->persist($demand);
        }

        $userWithFiveDemands = $this->getReference(UserFixture::USER_WITH_SLACK_SOCIAL_ACCOUNT, User::class);

        for ($i = 0; $i < 5; ++$i) {
            $demand = new Demand(
                $userWithFiveDemands,
                'demandify_postgres',
                \sprintf('demand content %d from user %s', $i, $userWithFiveDemands->email),
                \sprintf('demand reason %d from user %s', $i, $userWithFiveDemands->email)
            );
            $manager->persist($demand);
        }

        $manager->flush();
    }

    public function getDependencies(): array
    {
        return [
            UserFixture::class,
        ];
    }
}
