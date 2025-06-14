<?php

declare(strict_types=1);

namespace Demandify\Tests\Functional;

use Demandify\Tests\Fixtures\FixtureLoadable;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

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
}
