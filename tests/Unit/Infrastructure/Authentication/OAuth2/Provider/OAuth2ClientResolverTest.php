<?php

declare(strict_types=1);

namespace Demandify\Tests\Unit\Infrastructure\Authentication\OAuth2\Provider;

use Demandify\Domain\UserSocialAccount\UserSocialAccountType;
use Demandify\Infrastructure\Authentication\OAuth2\Provider\OAuth2Client;
use Demandify\Infrastructure\Authentication\OAuth2\Provider\OAuth2ClientResolver;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

/**
 * @internal
 */
#[CoversClass(OAuth2ClientResolver::class)]
final class OAuth2ClientResolverTest extends TestCase
{
    private MockObject|OAuth2Client $slackClient;
    private MockObject|OAuth2Client $googleClient;

    protected function setUp(): void
    {
        $this->slackClient = $this->createMock(OAuth2Client::class);
        $this->googleClient = $this->createMock(OAuth2Client::class);
    }

    public function testItResolvesCorrectClient(): void
    {
        $this->slackClient
            ->method('supports')
            ->willReturnCallback(static fn (UserSocialAccountType $type) => UserSocialAccountType::SLACK === $type)
        ;

        $this->googleClient
            ->method('supports')
            ->willReturnCallback(static fn (UserSocialAccountType $type) => UserSocialAccountType::GOOGLE === $type)
        ;

        $resolver = new OAuth2ClientResolver([$this->slackClient, $this->googleClient]);

        $resolved = $resolver->byType(UserSocialAccountType::GOOGLE);
        self::assertSame($this->googleClient, $resolved);

        $resolved = $resolver->byType(UserSocialAccountType::SLACK);
        self::assertSame($this->slackClient, $resolved);
    }

    public function testItThrowsIfNoClientFound(): void
    {
        $this->slackClient
            ->method('supports')
            ->willReturn(false)
        ;

        $resolver = new OAuth2ClientResolver([$this->slackClient]);

        $this->expectException(\RuntimeException::class);
        $this->expectExceptionMessage('OAuth2 client for type "google" not found');

        $resolver->byType(UserSocialAccountType::GOOGLE);
    }
}
