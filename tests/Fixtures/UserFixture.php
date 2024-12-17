<?php

declare(strict_types=1);

namespace Querify\Tests\Fixtures;

use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;
use Querify\Domain\User\Email;
use Querify\Domain\User\User;
use Querify\Domain\UserSocialAccount\UserSocialAccount;
use Querify\Domain\UserSocialAccount\UserSocialAccountType;

class UserFixture extends Fixture
{
    public const string USER_EMAIL_FIXTURE = 'example@local.host';
    public const string USER_NOT_ELIGIBLE_TO_APPROVE = 'not.eligible.user@local.host';
    public const string USER_WITH_SLACK_SOCIAL_ACCOUNT = 'user.with.slack.social.account@local.host';

    public function load(ObjectManager $manager): void
    {
        $user = new User(
            Email::fromString(self::USER_EMAIL_FIXTURE),
            'Name',
        );
        $notEligibleUser = new User(
            Email::fromString(self::USER_NOT_ELIGIBLE_TO_APPROVE),
            'Name',
        );
        $userWithSlackSocialAccount = new User(
            Email::fromString(self::USER_WITH_SLACK_SOCIAL_ACCOUNT),
            'Name'
        );
        $userWithSlackSocialAccount->linkSocialAccount(
            new UserSocialAccount(
                $userWithSlackSocialAccount,
                UserSocialAccountType::SLACK,
                'slackId',
            )
        );

        $manager->persist($user);
        $manager->persist($notEligibleUser);
        $manager->persist($userWithSlackSocialAccount);
        $this->addReference(self::USER_EMAIL_FIXTURE, $user);
        $manager->flush();
    }
}
