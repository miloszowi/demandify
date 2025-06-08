<?php

declare(strict_types=1);

namespace Demandify\Tests\Integration\Application\Query\IsUserEligibleToDecisionForExternalService;

use Demandify\Application\Query\IsUserEligibleToDecisionForExternalService\IsUserEligibleToDecisionForExternalService;
use Demandify\Application\Query\IsUserEligibleToDecisionForExternalService\IsUserEligibleToDecisionForExternalServiceHandler;
use Demandify\Domain\User\Email;
use Demandify\Domain\User\UserRepository;
use Demandify\Tests\Fixtures\ExternalServiceConfigurationFixture;
use Demandify\Tests\Fixtures\UserFixture;
use Demandify\Tests\Integration\BaseKernelTestCase;
use PHPUnit\Framework\Attributes\CoversClass;

/**
 * @internal
 */
#[CoversClass(IsUserEligibleToDecisionForExternalServiceHandler::class)]
final class IsUserEligibleToDecisionForExternalServiceHandlerTest extends BaseKernelTestCase
{
    private IsUserEligibleToDecisionForExternalServiceHandler $handler;
    private UserRepository $userRepository;

    protected function setUp(): void
    {
        parent::setUp();

        $this->handler = self::getContainer()->get(IsUserEligibleToDecisionForExternalServiceHandler::class);
        $this->userRepository = self::getContainer()->get(UserRepository::class);

        $this->load([new ExternalServiceConfigurationFixture()]);
    }

    public function testItReturnsTrueIfUserIsEligibleToDecisionForExternalService(): void
    {
        $eligibleUser = $this->userRepository->getByEmail(
            Email::fromString(UserFixture::USER_EMAIL_FIXTURE)
        );
        $query = new IsUserEligibleToDecisionForExternalService(
            $eligibleUser,
            'demandify_postgres'
        );

        $result = $this->handler->__invoke($query);

        self::assertTrue($result);
    }

    public function testItReturnsFalseIfUserIsNotEligibleToDecisionForExternalService(): void
    {
        $notEligibleUser = $this->userRepository->getByEmail(
            Email::fromString(UserFixture::USER_NOT_ELIGIBLE_TO_APPROVE)
        );
        $query = new IsUserEligibleToDecisionForExternalService(
            $notEligibleUser,
            'demandify_postgres'
        );

        $result = $this->handler->__invoke($query);

        self::assertFalse($result);
    }

    public function testItReturnsFalseIfExternalServiceConfigurationDoesNotExist(): void
    {
        $notEligibleUser = $this->userRepository->getByEmail(
            Email::fromString(UserFixture::USER_EMAIL_FIXTURE)
        );
        $query = new IsUserEligibleToDecisionForExternalService(
            $notEligibleUser,
            'not_existing_external_service'
        );

        $result = $this->handler->__invoke($query);

        self::assertFalse($result);
    }
}
