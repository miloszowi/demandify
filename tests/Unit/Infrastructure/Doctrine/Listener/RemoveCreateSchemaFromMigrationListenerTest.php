<?php

declare(strict_types=1);

namespace Demandify\Tests\Unit\Infrastructure\Doctrine\Listener;

use Demandify\Infrastructure\Doctrine\Listener\RemoveCreateSchemaFromMigrationListener;
use Doctrine\DBAL\Connection;
use Doctrine\DBAL\Schema\PostgreSQLSchemaManager;
use Doctrine\DBAL\Schema\Schema;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\Tools\Event\GenerateSchemaEventArgs;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;

/**
 * @internal
 */
#[CoversClass(RemoveCreateSchemaFromMigrationListener::class)]
final class RemoveCreateSchemaFromMigrationListenerTest extends TestCase
{
    public function testPostGenerateSchemaCreatesMissingNamespaces(): void
    {
        $namespaces = ['test'];

        $schemaManager = $this->createMock(PostgreSQLSchemaManager::class);
        $schemaManager->method('getExistingSchemaSearchPaths')->willReturn($namespaces);

        $connection = $this->createMock(Connection::class);
        $connection->method('getSchemaManager')->willReturn($schemaManager);

        $em = $this->createMock(EntityManagerInterface::class);
        $em->method('getConnection')->willReturn($connection);

        $schema = $this->createMock(Schema::class);
        $schema->expects(self::once())
            ->method('hasNamespace')
            ->with('test')
            ->willReturn(false)
        ;

        $schema->expects(self::once())
            ->method('createNamespace')
            ->with('test')
        ;

        $args = $this->createMock(GenerateSchemaEventArgs::class);
        $args->method('getEntityManager')->willReturn($em);
        $args->method('getSchema')->willReturn($schema);

        $listener = new RemoveCreateSchemaFromMigrationListener();
        $listener->postGenerateSchema($args);
    }
}
