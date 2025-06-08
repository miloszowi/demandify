<?php

declare(strict_types=1);

namespace Demandify\Tests\Functional;

use Demandify\Domain\User\Email;
use Demandify\Domain\User\UserRepository;
use Demandify\Tests\Fixtures\FixtureLoadable;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Test\TestBrowserToken;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Component\BrowserKit\Cookie;
use Symfony\Component\HttpFoundation\Session\SessionInterface;

abstract class BaseWebTestCase extends WebTestCase
{
    use FixtureLoadable;

    protected function setUp(): void
    {
        parent::setUp();
        $this->tearDown();
    }

    public function getEntityManager(): EntityManagerInterface
    {
        return self::getContainer()->get('doctrine')->getManager();
    }

    public function logIn(Email $email): void
    {
        $session = self::getContainer()->get(SessionInterface::class);

        $user = self::getContainer()->get(UserRepository::class)->getByEmail($email);
        $firewallName = 'main';

        $token = new TestBrowserToken($user->getRoles(), $user, $firewallName);
        $session->set('_security_'.$firewallName, $token);
        $session->save();

        $cookie = new Cookie($session->getName(), $session->getId());
        $this->getClient()->getCookieJar()->set($cookie);
    }
}
