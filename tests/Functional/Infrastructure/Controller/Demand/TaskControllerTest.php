<?php

declare(strict_types=1);

namespace Demandify\Tests\Functional\Infrastructure\Controller\Demand;

use Demandify\Domain\Demand\Demand;
use Demandify\Domain\Demand\DemandRepository;
use Demandify\Domain\Demand\Status;
use Demandify\Domain\User\Email;
use Demandify\Domain\User\User;
use Demandify\Domain\User\UserRepository;
use Demandify\Infrastructure\Controller\Demand\TaskController;
use Demandify\Tests\Fixtures\TestCase;
use Demandify\Tests\Functional\BaseWebTestCase;
use PHPUnit\Framework\Attributes\CoversClass;
use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\HttpFoundation\Response;

/**
 * @internal
 */
#[CoversClass(TaskController::class)]
final class TaskControllerTest extends BaseWebTestCase
{
    private UserRepository $userRepository;

    private DemandRepository $demandRepository;

    private Demand $executedDemand;
    private Demand $failedDemand;

    protected function setUp(): void
    {
        $this->userRepository = self::getContainer()->get(UserRepository::class);
        $this->demandRepository = self::getContainer()->get(DemandRepository::class);

        $this->load(
            [new TestCase\TaskControllerFixture()]
        );

        $this->executedDemand = $this->demandRepository->findInStatus(Status::EXECUTED)[0];
        $this->failedDemand = $this->demandRepository->findInStatus(Status::FAILED)[0];

        parent::setUp();
    }

    public function getUserForThisTest(): User
    {
        return $this->userRepository->getByEmail(
            Email::fromString(TestCase\TaskControllerFixture::USER_EMAIL)
        );
    }

    public function testTaskResult(): void
    {
        $client = self::createClient();
        $client->loginUser($this->getUserForThisTest());

        /** @var Filesystem $fileSystem */
        $fileSystem = self::getContainer()->get('filesystem');
        $fileSystem->mkdir($_ENV['APP_RESULTS_PATH']);
        $fileSystem->touch($this->executedDemand->task->resultPath);
        $fileSystem->appendToFile($this->executedDemand->task->resultPath, 'test-content');

        $client->request('GET', '/demand/'.$this->executedDemand->uuid->toString().'/task');
        self::assertResponseIsSuccessful();
        self::assertStringContainsString('test-content', $client->getInternalResponse()->getContent());
        $fileSystem->remove($this->executedDemand->task->resultPath);
    }

    public function testTaskResultDownload(): void
    {
        $client = self::createClient();
        $client->loginUser($this->getUserForThisTest());

        $fileSystem = self::getContainer()->get('filesystem');
        $fileSystem->mkdir($_ENV['APP_RESULTS_PATH']);
        $fileSystem->touch($this->executedDemand->task->resultPath);
        $fileSystem->appendToFile($this->executedDemand->task->resultPath, 'test-content');

        $client->request('GET', '/demand/'.$this->executedDemand->uuid->toString().'/task/download');
        self::assertResponseIsSuccessful();
        self::assertStringContainsString('test-content', $client->getInternalResponse()->getContent());
        $fileSystem->remove($this->executedDemand->task->resultPath);
    }

    public function testTaskResultForNotExistingFile(): void
    {
        $client = self::createClient();
        $client->loginUser($this->getUserForThisTest());

        $client->request('GET', '/demand/'.$this->executedDemand->uuid->toString().'/task');
        self::assertResponseStatusCodeSame(Response::HTTP_NOT_FOUND);
    }

    public function testTaskResultForFailedDemand(): void
    {
        $client = self::createClient();
        $client->loginUser($this->getUserForThisTest());

        $client->request('GET', '/demand/'.$this->failedDemand->uuid->toString().'/task');
        self::assertResponseStatusCodeSame(Response::HTTP_NOT_FOUND);
    }
}
