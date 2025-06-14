<?php

declare(strict_types=1);

namespace Demandify\Tests\Functional\Infrastructure\Controller;

use Demandify\Domain\User\Email;
use Demandify\Domain\User\User;
use Demandify\Domain\User\UserRepository;
use Demandify\Infrastructure\Controller\HomeController;
use Demandify\Tests\Fixtures\UserFixture;
use Demandify\Tests\Functional\BaseWebTestCase;
use PHPUnit\Framework\Attributes\CoversClass;
use Symfony\Component\HttpFoundation\Response;

/**
 * @internal
 */
#[CoversClass(HomeController::class)]
final class HomeControllerTest extends BaseWebTestCase
{
    protected function setUp(): void
    {
        $this->load(
            [new UserFixture()]
        );

        parent::setUp();
    }

    public function testHomePageWithRenderAndFormSubmission(): void
    {
        $client = self::createClient();
        $user = $this->getUserForThisTest();
        $client->loginUser($user);

        $client->request('GET', '/');

        self::assertResponseIsSuccessful();
        self::assertSelectorExists('form[name="demand_form"]');
        self::assertSelectorExists('select#demand_form_service');
        self::assertSelectorExists('input#demand_form_content');
        self::assertSelectorExists('input#demand_form_reason');

        $csrfToken = $client->getCrawler()->filter('#demand_form__token')->attr('value');

        $client->request('POST', '/', [
            'demand_form' => [
                'service' => 'demandify_postgres',
                'content' => 'test content',
                'reason' => 'test reason',
                '_token' => $csrfToken,
            ],
        ]);

        self::assertResponseRedirects();
        self::assertResponseStatusCodeSame(Response::HTTP_FOUND);
        self::assertCount(1, self::getContainer()->get('messenger.transport.async')->getSent());
    }

    public function testHomePageWithoutLogin(): void
    {
        $client = self::createClient();

        $client->request('GET', '/');

        self::assertResponseStatusCodeSame(Response::HTTP_FOUND);
    }

    public function testHomePageWithInvalidFormSubmission(): void
    {
        $client = self::createClient();
        $user = $this->getUserForThisTest();
        $client->loginUser($user);

        $client->request('POST', '/', [
            'demand_form' => [
                'service' => 'invalid_service',
                'content' => '',
                'reason' => '',
            ],
        ]);

        self::assertResponseStatusCodeSame(Response::HTTP_UNPROCESSABLE_ENTITY);
    }

    /**
     * TODO: Create some re-usable method for functional tests with similar methods.
     */
    private function getUserForThisTest(): User
    {
        return self::getContainer()->get(UserRepository::class)
            ->getByEmail(Email::fromString(UserFixture::USER_EMAIL_FIXTURE))
        ;
    }
}
