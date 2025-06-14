<?php

declare(strict_types=1);

namespace Demandify\Tests\Functional\Infrastructure\Controller\OAuth;

use Demandify\Infrastructure\Controller\OAuth\OAuthController;
use Demandify\Tests\Functional\BaseWebTestCase;
use PHPUnit\Framework\Attributes\CoversClass;
use Symfony\Component\HttpFoundation\Response;

/**
 * @internal
 */
#[CoversClass(OAuthController::class)]
final class OAuthControllerTest extends BaseWebTestCase
{
    protected function setUp(): void
    {
        parent::setUp();
    }

    public function testItRedirectsToTheOauthProvider(): void
    {
        $client = self::createClient();
        $client->request('GET', '/oauth/slack');

        self::assertResponseStatusCodeSame(Response::HTTP_FOUND);
        self::assertStringContainsString(
            'https://slack.com/oauth/v2/authorize?redirect_uri=http%3A%2F%2Flocalhost%2Foauth%2Fslack%2Fcheck&client_id=slack-client-id&user_scope=team%3Aread%2Cidentify&state=',
            $client->getResponse()->headers->get('Location')
        );
    }

    public function testItReturnsNotFoundForNonExistingProvider(): void
    {
        $client = self::createClient();
        $client->request('GET', '/oauth/non-existing-provider');
        self::assertResponseStatusCodeSame(Response::HTTP_NOT_FOUND);
    }

    public function testItReturnsBadRequestForCheckWithoutStoredState(): void
    {
        $client = self::createClient();
        $client->request(
            'GET',
            '/oauth/slack/check?code=test-code&state=test-state'
        );

        self::assertResponseStatusCodeSame(Response::HTTP_BAD_REQUEST);
    }
}
