<?php

declare(strict_types=1);

namespace Demandify\Tests\Fixtures\ExternalServiceConfiguration;

use Demandify\Domain\ExternalService\ExternalServiceConfiguration;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;

class ExternalServiceConfigurationWithoutEligibleApproversFixture extends Fixture
{
    public function load(ObjectManager $manager): void
    {
        $externalServiceConfiguration = new ExternalServiceConfiguration(
            'demandify_postgres',
            []
        );

        $manager->persist($externalServiceConfiguration);
        $manager->flush();
    }
}
