<?php

declare(strict_types=1);

namespace Querify\Tests\Fixtures;

use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;
use Querify\Domain\User\Email;
use Querify\Domain\User\User;

class UserFixture extends Fixture
{
    public const string USER_EMAIL_FIXTURE = 'example@local.host';

    public function load(ObjectManager $manager): void
    {
        $user = new User(
            Email::fromString(self::USER_EMAIL_FIXTURE),
            'Name',
        );

        $manager->persist($user);
        $manager->flush();
    }
}
