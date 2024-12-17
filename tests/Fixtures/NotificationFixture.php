<?php

declare(strict_types=1);

namespace Querify\Tests\Fixtures;

use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Common\DataFixtures\DependentFixtureInterface;
use Doctrine\Persistence\ObjectManager;
use Querify\Domain\Demand\Demand;
use Querify\Domain\Notification\Notification;
use Querify\Domain\Notification\NotificationType;
use Querify\Domain\UserSocialAccount\UserSocialAccountType;

class NotificationFixture extends Fixture implements DependentFixtureInterface
{
    public const string NOTIFICATION_IDENTIFIER = '123456789.123456789';

    public function load(ObjectManager $manager): void
    {
        $notification = new Notification(
            $this->getReference(DemandFixture::APPROVED_DEMAND_FIXTURE_KEY, Demand::class)->uuid,
            NotificationType::NEW_DEMAND,
            self::NOTIFICATION_IDENTIFIER,
            'content sent to user',
            [
                [
                    'some_attachment' => 'some attachment value',
                ],
            ],
            'channel',
            UserSocialAccountType::SLACK,
        );

        $manager->persist($notification);
        $manager->flush();
    }

    public function getDependencies(): array
    {
        return [
            DemandFixture::class,
        ];
    }
}
