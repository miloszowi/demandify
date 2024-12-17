<?php

declare(strict_types=1);

namespace Querify\Tests\Integration\Application\Command\LinkSocialAccountToUser;

use Querify\Application\Command\LinkSocialAccountToUser\LinkSocialAccountToUser;
use Querify\Application\Command\LinkSocialAccountToUser\LinkSocialAccountToUserHandler;
use Querify\Domain\User\Email;
use Querify\Domain\User\Exception\UserNotFoundException;
use Querify\Domain\User\UserRepository;
use Querify\Domain\User\UserRole;
use Querify\Domain\UserSocialAccount\UserSocialAccountType;
use Querify\Tests\Fixtures\UserFixture;
use Querify\Tests\integration\BaseKernelTestCase;

/**
 * @internal
 *
 * @covers \Querify\Application\Command\LinkSocialAccountToUser\LinkSocialAccountToUserHandler
 */
final class LinkSocialAccountToUserHandlerTest extends BaseKernelTestCase
{
    private LinkSocialAccountToUserHandler $handler;
    private UserRepository $userRepository;

    protected function setUp(): void
    {
        parent::setUp();

        $this->handler = self::getContainer()->get(LinkSocialAccountToUserHandler::class);
        $this->userRepository = self::getContainer()->get(UserRepository::class);

        $this->load([new UserFixture()]);
    }

    public function testLinkingSocialAccountIsSuccessful(): void
    {
        $command = new LinkSocialAccountToUser(
            UserFixture::USER_EMAIL_FIXTURE,
            'name from slack',
            UserSocialAccountType::SLACK,
            'externalId',
            [
                'some_data' => 'even_more_data',
            ],
        );

        $user = $this->userRepository->getByEmail(Email::fromString(UserFixture::USER_EMAIL_FIXTURE));
        self::assertCount(0, $user->getSocialAccounts());

        $this->handler->__invoke($command);
        $socialAccount = $user->getSocialAccount(UserSocialAccountType::SLACK);

        self::assertCount(1, $user->getSocialAccounts());
        self::assertSame(UserSocialAccountType::SLACK, $socialAccount->type);
        self::assertSame($user, $socialAccount->user);
        self::assertSame('externalId', $socialAccount->externalId);
        self::assertSame(
            [
                'some_data' => 'even_more_data',
            ],
            $socialAccount->extraData
        );
    }

    public function testLinkingSocialAccountForNewUserRegisterHim(): void
    {
        $newUserEmail = 'non-existing@local.host';

        $command = new LinkSocialAccountToUser(
            $newUserEmail,
            'name from slack',
            UserSocialAccountType::SLACK,
            'externalId',
            [
                'some_data' => 'even_more_data',
            ],
        );

        self::expectException(UserNotFoundException::class);
        $this->userRepository->getByEmail(Email::fromString($newUserEmail));

        $this->handler->__invoke($command);
        $user = $this->userRepository->getByEmail(Email::fromString($newUserEmail));
        self::assertSame('name from slack', $user->name);
        self::assertSame([UserRole::ROLE_USER], $user->getRoles());

        $socialAccount = $user->getSocialAccount(UserSocialAccountType::SLACK);
        self::assertCount(1, $user->getSocialAccounts());
        self::assertSame(UserSocialAccountType::SLACK, $socialAccount->type);
        self::assertSame($user, $socialAccount->user);
        self::assertSame('externalId', $socialAccount->externalId);
        self::assertSame(
            [
                'some_data' => 'even_more_data',
            ],
            $socialAccount->extraData
        );
    }
}
