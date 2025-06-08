<?php

declare(strict_types=1);

namespace Demandify\Tests\Functional\Infrastructure\Controller\Demand;

use Demandify\Domain\Demand\Demand;
use Demandify\Domain\Demand\DemandRepository;
use Demandify\Domain\Demand\Status;
use Demandify\Domain\ExternalService\ExternalServiceConfigurationRepository;
use Demandify\Domain\User\Email;
use Demandify\Domain\User\User;
use Demandify\Domain\User\UserRepository;
use Demandify\Infrastructure\Controller\Demand\DemandController;
use Demandify\Tests\Fixtures\TestCase;
use Demandify\Tests\Functional\BaseWebTestCase;
use PHPUnit\Framework\Attributes\CoversClass;
use Symfony\Component\HttpFoundation\Response;

/**
 * @internal
 */
#[CoversClass(DemandController::class)]
final class DemandControllerTest extends BaseWebTestCase
{
    private UserRepository $userRepository;
    private DemandRepository $demandRepository;
    private Demand $demand;

    protected function setUp(): void
    {
        $this->userRepository = self::getContainer()->get(UserRepository::class);
        $this->demandRepository = self::getContainer()->get(DemandRepository::class);

        $this->load(
            [new TestCase\DemandControllerFixture()]
        );

        $this->demand = $this->demandRepository->findInStatus(Status::NEW)[0];

        parent::setUp();
    }

    public function getUserForThisTest(): User
    {
        return $this->userRepository->getByEmail(
            Email::fromString(TestCase\DemandControllerFixture::USER_EMAIL)
        );
    }

    public function testViewDemand(): void
    {
        $client = self::createClient();
        $client->loginUser($this->getUserForThisTest());

        $client->request('GET', '/demand/'.$this->demand->uuid->toString());

        self::assertResponseIsSuccessful();
        self::assertSelectorTextContains('h2', 'Demand Details');
        self::assertSelectorTextContains('dd.col-sm-9', $this->demand->uuid->toString());
    }

    public function testCanNotViewDemandIfUserIsNotLoggedIn(): void
    {
        $client = self::createClient();

        $client->request('GET', '/demand/'.$this->demand->uuid->toString());

        self::assertResponseRedirects('/login');
    }

    public function testUserDemands(): void
    {
        $client = self::createClient();
        $client->loginUser($this->getUserForThisTest());

        $client->request('GET', '/demands');
        self::assertResponseIsSuccessful();
        self::assertSelectorTextContains('h1', 'My Demands');
        self::assertSelectorCount(3, 'tbody tr');
    }

    public function testUserDemandsIfUserIsNotLoggedIn(): void
    {
        $client = self::createClient();
        $client->request('GET', '/demands');
        self::assertResponseRedirects('/login');
    }

    public function testReviewDemands(): void
    {
        $client = self::createClient();
        $client->loginUser($this->getUserForThisTest());

        $client->request('GET', '/demands/review');

        self::assertResponseIsSuccessful();
        self::assertSelectorTextContains('h1', 'Review Demands');
        self::assertSelectorCount(2, 'tbody tr');
    }

    public function testApproveDemand(): void
    {
        $client = self::createClient();
        $user = $this->getUserForThisTest();
        $client->loginUser($user);

        $configuration = self::getContainer()->get(ExternalServiceConfigurationRepository::class)->findForUser($user->uuid);
        $demand = $this->demandRepository->findDemandsAwaitingDecisionForServices($user->uuid, $configuration)[0];

        $client->request('POST', '/demands/'.$demand->uuid->toString().'/approve');
        self::assertResponseRedirects('/demands/review');
    }

    public function testCanNotApproveDemandIfNotEligible(): void
    {
        $client = self::createClient();
        $user = $this->getUserForThisTest();
        $client->loginUser($user);

        $client->request('POST', '/demands/'.$this->demand->uuid->toString().'/approve');
        self::assertResponseStatusCodeSame(Response::HTTP_FORBIDDEN);
    }

    public function testCanNotApproveDemandIfUserIsNotLoggedIn(): void
    {
        $client = self::createClient();
        $client->request('POST', '/demands/'.$this->demand->uuid->toString().'/approve');
        self::assertResponseRedirects('/login');
    }

    public function testDeclineDemand(): void
    {
        $client = self::createClient();
        $user = $this->getUserForThisTest();
        $client->loginUser($user);

        $configuration = self::getContainer()->get(ExternalServiceConfigurationRepository::class)->findForUser($user->uuid);
        $demand = $this->demandRepository->findDemandsAwaitingDecisionForServices($user->uuid, $configuration)[0];

        $client->request('POST', '/demands/'.$demand->uuid->toString().'/decline');
        self::assertResponseRedirects('/demands/review');
    }

    public function testCanNotDeclineDemandIfNotEligible(): void
    {
        $client = self::createClient();
        $user = $this->getUserForThisTest();
        $client->loginUser($user);

        $client->request('POST', '/demands/'.$this->demand->uuid->toString().'/decline');
        self::assertResponseStatusCodeSame(Response::HTTP_FORBIDDEN);
    }

    public function testCanNotDeclineDemandIfUserIsNotLoggedIn(): void
    {
        $client = self::createClient();
        $client->request('POST', '/demands/'.$this->demand->uuid->toString().'/decline');
        self::assertResponseRedirects('/login');
    }
}
