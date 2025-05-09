<?php

declare(strict_types=1);

namespace Demandify\Tests\Fixtures;

use Demandify\Domain\Demand\Demand;
use Demandify\Domain\Notification\Notification;
use Demandify\Domain\Notification\NotificationType;
use Demandify\Domain\UserSocialAccount\UserSocialAccountType;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Common\DataFixtures\DependentFixtureInterface;
use Doctrine\Persistence\ObjectManager;

class NotificationFixture extends Fixture implements DependentFixtureInterface
{
    public const string NOTIFICATION_IDENTIFIER = '123456789.123456789';
    public const string DECLINED_DEMAND_NOTIFICATION_IDENTIFIER = 'declined.123456789.123456789';

    public function load(ObjectManager $manager): void
    {
        $notification = new Notification(
            $this->getReference(DemandFixture::APPROVED_DEMAND_FIXTURE_KEY, Demand::class)->uuid,
            NotificationType::NEW_DEMAND,
            'recipient',
            self::NOTIFICATION_IDENTIFIER,
            [
                [
                    'some_attachment' => 'some attachment value',
                ],
            ],
            UserSocialAccountType::SLACK,
        );

        $declinedDemandNotification = new Notification(
            $this->getReference(DemandFixture::DECLINED_DEMAND_FIXTURE_KEY, Demand::class)->uuid,
            NotificationType::NEW_DEMAND,
            'recipient2',
            self::DECLINED_DEMAND_NOTIFICATION_IDENTIFIER,
            [
                [
                    'some_attachment' => 'some attachment value',
                ],
            ],
            UserSocialAccountType::SLACK,
        );

        $manager->persist($notification);
        $manager->persist($declinedDemandNotification);
        $manager->flush();
    }

    public function getDependencies(): array
    {
        return [
            DemandFixture::class,
        ];
    }
}
