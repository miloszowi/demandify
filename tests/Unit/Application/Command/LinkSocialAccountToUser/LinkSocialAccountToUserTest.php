<?php

declare(strict_types=1);

namespace Demandify\Tests\Unit\Application\Command\LinkSocialAccountToUser;

use Demandify\Application\Command\LinkSocialAccountToUser\LinkSocialAccountToUser;
use Demandify\Domain\UserSocialAccount\UserSocialAccountType;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;

/**
 * @internal
 */
#[CoversClass(LinkSocialAccountToUser::class)]
final class LinkSocialAccountToUserTest extends TestCase
{
    public function testIsInitializable(): void
    {
        $command = new LinkSocialAccountToUser(
            'email@local.host',
            UserSocialAccountType::SLACK,
            'external-id',
            ['test-key' => 'test-value']
        );

        self::assertInstanceOf(LinkSocialAccountToUser::class, $command);
    }
}
