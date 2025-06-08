<?php

declare(strict_types=1);

namespace Demandify\Tests\Integration;

use Demandify\Domain\User\Email;
use Demandify\Domain\User\UserRepository;
use Demandify\Tests\Fixtures\FixtureLoadable;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;
use Symfony\Component\Messenger\Transport\InMemory\InMemoryTransport;
use Symfony\Component\Security\Core\Authentication\Token\NullToken;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Authentication\Token\UsernamePasswordToken;

/**
 * @internal
 *
 * @coversNothing
 */
abstract class BaseKernelTestCase extends KernelTestCase
{
    use FixtureLoadable;

    public function getEntityManager(): EntityManagerInterface
    {
        return self::getContainer()->get('doctrine')->getManager();
    }

    public function getAsyncTransport(): InMemoryTransport
    {
        return $this->getContainer()->get('messenger.transport.async');
    }

    public function getTransport(string $transport): InMemoryTransport
    {
        return $this->getContainer()->get('messenger.transport.'.$transport);
    }

    public function getTokenByUserEmail(Email $email): TokenInterface
    {
        $user = self::getContainer()->get(UserRepository::class)->getByEmail($email);
        $firewallName = 'main';

        return new UsernamePasswordToken($user, $firewallName, $user->getRoles());
    }

    public function getNullToken(): TokenInterface
    {
        return new NullToken();
    }
}
