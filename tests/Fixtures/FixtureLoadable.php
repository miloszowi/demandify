<?php

declare(strict_types=1);

namespace Demandify\Tests\Fixtures;

use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Common\DataFixtures\Executor\ORMExecutor;
use Doctrine\Common\DataFixtures\Loader;
use Doctrine\Common\DataFixtures\Purger\ORMPurger;
use Doctrine\ORM\EntityManagerInterface;

trait FixtureLoadable
{
    /**
     * @param Fixture[] $fixtures
     */
    public function load(array $fixtures): void
    {
        $loader = new Loader();

        foreach ($fixtures as $fixture) {
            $loader->addFixture($fixture);
        }

        $executor = new ORMExecutor($this->getEntityManager(), new ORMPurger($this->getEntityManager()));
        $executor->execute($loader->getFixtures());
    }

    abstract public function getEntityManager(): EntityManagerInterface;
}
