<?php

declare(strict_types=1);

namespace Querify\Tests\Fixtures;

use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Common\DataFixtures\DependentFixtureInterface;
use Doctrine\Persistence\ObjectManager;
use Querify\Domain\ExternalService\ExternalServiceConfiguration;
use Querify\Domain\User\User;

class ExternalServiceConfigurationFixture extends Fixture implements DependentFixtureInterface
{
    public function load(ObjectManager $manager): void
    {
        $externalServiceConfiguration = new ExternalServiceConfiguration(
            'querify_postgres',
            [$this->getReference(UserFixture::USER_EMAIL_FIXTURE, User::class)->uuid]
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
