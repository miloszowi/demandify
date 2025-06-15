<?php

declare(strict_types=1);

namespace Demandify\Infrastructure\Doctrine\Listener;

use Doctrine\ORM\Tools\Event\GenerateSchemaEventArgs;

class RemoveCreateSchemaFromMigrationListener
{
    /**
     * Prevents Doctrine from adding "CREATE SCHEMA public" to every down migration.
     */
    public function postGenerateSchema(GenerateSchemaEventArgs $args): void
    {
        $schemaManager = $args
            ->getEntityManager()
            ->getConnection()
            ->getSchemaManager()
        ;

        $schema = $args->getSchema();

        foreach ($schemaManager->getExistingSchemaSearchPaths() as $namespace) { // @phpstan-ignore-line
            if (!$schema->hasNamespace($namespace)) {
                $schema->createNamespace($namespace);
            }
        }
    }
}
