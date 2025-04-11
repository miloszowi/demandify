<?php

declare(strict_types=1);

namespace Demandify\Tests\Fixtures;

use Demandify\Domain\ExternalService\ExternalServiceConfiguration;
use Demandify\Domain\User\User;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Common\DataFixtures\DependentFixtureInterface;
use Doctrine\Persistence\ObjectManager;

class ExternalServiceConfigurationFixture extends Fixture implements DependentFixtureInterface
{
    public function load(ObjectManager $manager): void
    {
        $externalServiceConfiguration = new ExternalServiceConfiguration(
            'demandify_postgres',
            [$this->getReference(UserFixture::USER_EMAIL_FIXTURE, User::class)->uuid->toString()]
        );

        $manager->persist($externalServiceConfiguration);
        $manager->flush();
    }

    public function getDependencies(): array
    {
        return [
            UserFixture::class,
        ];
    }
}
