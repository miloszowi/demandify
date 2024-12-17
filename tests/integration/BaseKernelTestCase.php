<?php

declare(strict_types=1);

namespace Querify\Tests\integration;

use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Common\DataFixtures\Executor\ORMExecutor;
use Doctrine\Common\DataFixtures\Loader;
use Doctrine\Common\DataFixtures\Purger\ORMPurger;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;
use Symfony\Component\Messenger\Transport\InMemory\InMemoryTransport;

/**
 * @internal
 *
 * @coversNothing
 */
abstract class BaseKernelTestCase extends KernelTestCase
{
    protected EntityManagerInterface $entityManager;

    protected function setUp(): void
    {
        parent::setUp();

        $this->entityManager = self::getContainer()->get('doctrine')->getManager();
    }

    /**
     * @param Fixture[] $fixtures
     */
    public function load(array $fixtures): void
    {
        $loader = new Loader();

        foreach ($fixtures as $fixture) {
            $loader->addFixture($fixture);
        }

        $executor = new ORMExecutor($this->entityManager, new ORMPurger($this->entityManager));
        $executor->execute($loader->getFixtures());
    }

    public function getAsyncTransport(): InMemoryTransport
    {
        return $this->getContainer()->get('messenger.transport.async');
    }
}
