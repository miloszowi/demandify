<?php

declare(strict_types=1);

namespace Demandify\Tests\Integration\Application\Query\GetDemandsToBeReviewedForUser;

use Demandify\Application\Query\GetDemandsToBeReviewedForUser\GetDemandsToBeReviewedForUser;
use Demandify\Application\Query\GetDemandsToBeReviewedForUser\GetDemandsToBeReviewedForUserHandler;
use Demandify\Domain\User\Email;
use Demandify\Domain\User\UserRepository;
use Demandify\Tests\Fixtures\Demand\ManyDemandsFixture;
use Demandify\Tests\Fixtures\ExternalServiceConfigurationFixture;
use Demandify\Tests\Fixtures\UserFixture;
use Demandify\Tests\Integration\BaseKernelTestCase;
use PHPUnit\Framework\Attributes\CoversClass;

/**
 * @internal
 */
#[CoversClass(GetDemandsToBeReviewedForUserHandler::class)]
final class GetDemandsToBeReviewedForUserHandlerTest extends BaseKernelTestCase
{
    private GetDemandsToBeReviewedForUserHandler $handler;
    private UserRepository $userRepository;

    protected function setUp(): void
    {
        parent::setUp();

        $this->handler = self::getContainer()->get(GetDemandsToBeReviewedForUserHandler::class);
        $this->userRepository = self::getContainer()->get(UserRepository::class);

        $this->load([new ManyDemandsFixture(), new ExternalServiceConfigurationFixture()]);
    }

    public function testItReturnsDemandsToBeReviewedForUser(): void
    {
        $user = $this->userRepository->getByEmail(
            Email::fromString(UserFixture::USER_EMAIL_FIXTURE)
        );

        $query = new GetDemandsToBeReviewedForUser($user->uuid);

        $result = $this->handler->__invoke($query);
        self::assertCount(15, $result);
        // TODO: Add pagination & search tests when it will be available in query
    }
}
