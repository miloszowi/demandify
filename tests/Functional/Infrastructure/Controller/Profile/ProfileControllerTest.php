<?php

declare(strict_types=1);

namespace Demandify\Tests\Functional\Infrastructure\Controller\Profile;

use Demandify\Domain\User\Email;
use Demandify\Domain\User\User;
use Demandify\Domain\User\UserRepository;
use Demandify\Infrastructure\Controller\User\ProfileController;
use Demandify\Tests\Fixtures\UserFixture;
use Demandify\Tests\Functional\BaseWebTestCase;
use PHPUnit\Framework\Attributes\CoversClass;

/**
 * @internal
 */
#[CoversClass(ProfileController::class)]
final class ProfileControllerTest extends BaseWebTestCase
{
    private UserRepository $userRepository;

    protected function setUp(): void
    {
        $this->userRepository = self::getContainer()->get(UserRepository::class);
        $this->load([new UserFixture()]);

        parent::setUp();
    }

    public function testItRendersUserIndex(): void
    {
        $client = self::createClient();
        $user = $this->getUserForThisTest();
        $client->loginUser($user);
        $client->request('GET', '/profile');

        self::assertResponseIsSuccessful();
        self::assertSelectorExists('input#user-email[value="'.$user->email.'"]');
    }

    public function testItTogglesSocialAccountNotifiability(): void
    {
        $client = self::createClient();
        $user = $this->getUserForThisTest();
        $client->loginUser($user);

        $client->request(
            'POST',
            '/profile/social-account/slack/notifiability',
            ['notifiability' => '0']
        );

        self::assertResponseRedirects('/profile');
        $client->followRedirect();

        self::assertSelectorExists('input#user-email[value="'.$user->email.'"]');
        // TODO: Fix me @see UserSocialAccountRepository
        //        self::assertFalse($user->getSocialAccount(UserSocialAccountType::SLACK)->isNotifiable());
    }

    private function getUserForThisTest(): User
    {
        return $this->userRepository->getByEmail(
            Email::fromString(UserFixture::USER_WITH_SLACK_SOCIAL_ACCOUNT)
        );
    }
}
