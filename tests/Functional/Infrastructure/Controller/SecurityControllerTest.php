<?php

declare(strict_types=1);

namespace Demandify\Tests\Functional\Infrastructure\Controller;

use Demandify\Infrastructure\Controller\SecurityController;
use Demandify\Tests\Functional\BaseWebTestCase;
use PHPUnit\Framework\Attributes\CoversClass;

/**
 * @internal
 */
#[CoversClass(SecurityController::class)]
final class SecurityControllerTest extends BaseWebTestCase
{
    public function testLoginView(): void
    {
        $client = self::createClient();
        $client->request('GET', '/login');

        self::assertResponseIsSuccessful();
        self::assertSelectorTextContains('h1', 'Welcome back');
        self::assertSelectorTextContains('.subtitle', 'Sign in to continue to Demandify');
    }

    public function testLogout(): void
    {
        $client = self::createClient();
        $client->request('GET', '/logout');
        $client->followRedirects();

        self::assertResponseRedirects('/login');
    }
}
