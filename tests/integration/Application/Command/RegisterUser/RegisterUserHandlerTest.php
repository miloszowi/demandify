<?php

declare(strict_types=1);

namespace Querify\Tests\Integration\Application\Command\RegisterUser;

use Querify\Application\Command\RegisterUser\RegisterUser;
use Querify\Application\Command\RegisterUser\RegisterUserHandler;
use Querify\Domain\User\Email;
use Querify\Domain\User\Exception\UserAlreadyRegisteredException;
use Querify\Domain\User\UserRepository;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Messenger\MessageBusInterface;

class RegisterUserHandlerTest extends KernelTestCase
{
    private EntityManagerInterface $entityManager;
    private RegisterUserHandler $handler;
    private UserRepository $userRepository;

    protected function setUp(): void
    {
        self::bootKernel();

        $this->entityManager = self::getContainer()->get(EntityManagerInterface::class);
        $this->userRepository = self::getContainer()->get(UserRepository::class);
        $this->handler = self::getContainer()->get(RegisterUserHandler::class);

        $this->entityManager->createQuery('DELETE FROM Querify\Domain\User\User')->execute();
    }

    public function testRegistersUserSuccessfully(): void
    {
        $command = new RegisterUser('new@example.com', 'John', 'Doe', 'password123', ['ROLE_USER']);

        $this->handler->__invoke($command);

        $savedUser = $this->userRepository->findByEmail(Email::fromString('new@example.com'));

        $this->assertNotNull($savedUser);
        $this->assertSame('new@example.com', (string)$savedUser->email);
    }

    public function testThrowsExceptionWhenUserWithEmailAlreadyExists(): void
    {
        $existingEmail = 'existing@example.com';

        $command1 = new RegisterUser($existingEmail, 'John', 'Doe', 'password123', ['ROLE_USER']);
        $this->handler->__invoke($command1);

        $command2 = new RegisterUser($existingEmail, 'Jane', 'Smith', 'password123', ['ROLE_USER']);

        $this->expectException(UserAlreadyRegisteredException::class);
        $this->expectExceptionMessage(sprintf('User with email "%s" is already registered.', $existingEmail));

        $this->handler->__invoke($command2);
    }
}
