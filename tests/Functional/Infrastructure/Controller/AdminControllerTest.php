<?php

declare(strict_types=1);

namespace Demandify\Tests\Functional\Infrastructure\Controller;

use Demandify\Domain\User\Email;
use Demandify\Domain\User\User;
use Demandify\Domain\User\UserRepository;
use Demandify\Infrastructure\Controller\AdminController;
use Demandify\Tests\Fixtures\ExternalServiceConfigurationFixture;
use Demandify\Tests\Fixtures\UserFixture;
use Demandify\Tests\Functional\BaseWebTestCase;
use PHPUnit\Framework\Attributes\CoversClass;
use Symfony\Component\HttpFoundation\Response;

/**
 * @internal
 */
#[CoversClass(AdminController::class)]
final class AdminControllerTest extends BaseWebTestCase
{
    protected function setUp(): void
    {
        $this->load([
            new UserFixture(),
            new ExternalServiceConfigurationFixture(),
        ]);
        parent::setUp();
    }

    public function testItRendersAdminIndex(): void
    {
        $client = self::createClient();
        $admin = $this->getAdminForThisTest();
        $client->loginUser($admin);

        $client->request('GET', '/admin');

        self::assertResponseIsSuccessful();
        self::assertSelectorTextContains('h3.service-name', 'demandify_postgres'); // dostosuj do szablonu
    }

    public function testItRedirectsForNonAdminUsers(): void
    {
        $client = self::createClient();

        $client->request('GET', '/admin');

        self::assertResponseStatusCodeSame(Response::HTTP_FOUND);
    }

    public function testItRendersEditServiceForm(): void
    {
        $client = self::createClient();
        $admin = $this->getAdminForThisTest();
        $client->loginUser($admin);

        $client->request('GET', '/admin/services/demandify_postgres');

        self::assertResponseIsSuccessful();
        self::assertSelectorExists(
            'input#external_service_configuration_form_externalServiceName[value="demandify_postgres"]'
        );
        self::assertSelectorTextContains(
            'option[selected="selected"]',
            UserFixture::USER_EMAIL_FIXTURE
        );
    }

    public function testItRendersEditServiceFormWithoutUsers(): void
    {
        $client = self::createClient();
        $admin = $this->getAdminForThisTest();
        $client->loginUser($admin);

        self::getEntityManager()->getConnection()->executeStatement('DELETE FROM external_service_configuration');

        $client->request('GET', '/admin/services/demandify_postgres');

        self::assertResponseIsSuccessful();
        self::assertSelectorExists(
            'input#external_service_configuration_form_externalServiceName[value="demandify_postgres"]'
        );
        self::assertSelectorNotExists('option[selected="selected"]');
    }

    public function testItReturnsNotFoundForNonExistingService(): void
    {
        $client = self::createClient();
        $admin = $this->getAdminForThisTest();
        $client->loginUser($admin);

        $client->request('GET', '/admin/services/non_existing_service');

        self::assertResponseStatusCodeSame(Response::HTTP_NOT_FOUND);
    }

    public function testItEditsServiceConfiguration(): void
    {
        $client = self::createClient();
        $admin = $this->getAdminForThisTest();
        $client->loginUser($admin);

        $crawler = $client->request('GET', '/admin/services/demandify_postgres');

        $form = $crawler->selectButton('Submit')->form([
            'external_service_configuration_form[eligibleApprovers]' => [$admin->uuid->toString()],
        ]);
        $client->submit($form);

        self::assertCount(1, self::getContainer()->get('messenger.transport.async')->getSent());

        self::assertResponseRedirects('/admin');
        $client->followRedirect();
    }

    private function getAdminForThisTest(): User
    {
        return self::getContainer()->get(UserRepository::class)
            ->getByEmail(Email::fromString(UserFixture::ADMIN_EMAIL_FIXTURE))
        ;
    }
}
