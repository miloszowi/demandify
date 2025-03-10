<?php

declare(strict_types=1);

namespace Demandify\Tests\Integration\Application\Command\RegisterUser;

use Demandify\Application\Command\RegisterUser\RegisterUser;
use Demandify\Application\Command\RegisterUser\RegisterUserHandler;
use Demandify\Domain\User\Email;
use Demandify\Domain\User\Exception\UserAlreadyRegisteredException;
use Demandify\Domain\User\UserRepository;
use Demandify\Domain\User\UserRole;
use Demandify\Tests\Fixtures\UserFixture;
use Demandify\Tests\Integration\BaseKernelTestCase;
use Doctrine\ORM\EntityManagerInterface;
use PHPUnit\Framework\Attributes\CoversClass;

/**
 * @internal
 */
#[CoversClass(RegisterUserHandler::class)]
final class RegisterUserHandlerTest extends BaseKernelTestCase
{
    private RegisterUserHandler $handler;
    private UserRepository $userRepository;

    protected function setUp(): void
    {
        self::bootKernel();

        $this->entityManager = self::getContainer()->get(EntityManagerInterface::class);
        $this->userRepository = self::getContainer()->get(UserRepository::class);
        $this->handler = self::getContainer()->get(RegisterUserHandler::class);

        $this->load([
            new UserFixture(),
        ]);
    }

    public function testRegistersUserSuccessfully(): void
    {
        $command = new RegisterUser('new@example.com', 'John', [UserRole::ROLE_USER]);

        $this->handler->__invoke($command);

        $savedUser = $this->userRepository->findByEmail(Email::fromString('new@example.com'));

        self::assertNotNull($savedUser);
        self::assertSame('new@example.com', (string) $savedUser->email);
    }

    public function testThrowsExceptionWhenUserWithEmailAlreadyExists(): void
    {
        $existingEmail = UserFixture::USER_EMAIL_FIXTURE;

        $command = new RegisterUser($existingEmail, 'John', [UserRole::ROLE_USER]);

        $this->expectException(UserAlreadyRegisteredException::class);
        $this->expectExceptionMessage(\sprintf('User with email "%s" is already registered.', $existingEmail));

        $this->handler->__invoke($command);
    }
}
