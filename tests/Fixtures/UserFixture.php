<?php

declare(strict_types=1);

namespace Demandify\Tests\Fixtures;

use Demandify\Domain\User\Email;
use Demandify\Domain\User\User;
use Demandify\Domain\UserSocialAccount\UserSocialAccount;
use Demandify\Domain\UserSocialAccount\UserSocialAccountType;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;

class UserFixture extends Fixture
{
    public const string USER_EMAIL_FIXTURE = 'example@local.host';
    public const string USER_NOT_ELIGIBLE_TO_APPROVE = 'not.eligible.user@local.host';
    public const string USER_WITH_SLACK_SOCIAL_ACCOUNT = 'user.with.slack.social.account@local.host';
    public const string USER_WITH_NOT_NOTIFIABLE_SOCIAL_ACCOUNT = 'user.with.not.notifiable.social.account@local.host';

    public function load(ObjectManager $manager): void
    {
        $user = new User(Email::fromString(self::USER_EMAIL_FIXTURE));

        $notEligibleUser = new User(Email::fromString(self::USER_NOT_ELIGIBLE_TO_APPROVE));

        $userWithSlackSocialAccount = new User(Email::fromString(self::USER_WITH_SLACK_SOCIAL_ACCOUNT));
        $socialAccount = new UserSocialAccount(
            $userWithSlackSocialAccount,
            UserSocialAccountType::SLACK,
            'slackId',
        );
        $socialAccount->setNotifiable(true);
        $userWithSlackSocialAccount->linkSocialAccount($socialAccount);

        $userWithNotNotifiableSocialAccount = new User(
            Email::fromString(self::USER_WITH_NOT_NOTIFIABLE_SOCIAL_ACCOUNT)
        );
        $socialAccount = new UserSocialAccount(
            $userWithNotNotifiableSocialAccount,
            UserSocialAccountType::SLACK,
            'slackId',
        );
        $socialAccount->setNotifiable(false);
        $userWithNotNotifiableSocialAccount->linkSocialAccount($socialAccount);

        $manager->persist($user);
        $manager->persist($notEligibleUser);
        $manager->persist($userWithSlackSocialAccount);
        $manager->persist($userWithNotNotifiableSocialAccount);
        $this->addReference(self::USER_EMAIL_FIXTURE, $user);
        $this->addReference(self::USER_WITH_SLACK_SOCIAL_ACCOUNT, $userWithSlackSocialAccount);
        $this->addReference(self::USER_WITH_NOT_NOTIFIABLE_SOCIAL_ACCOUNT, $userWithNotNotifiableSocialAccount);
        $manager->flush();
    }
}
