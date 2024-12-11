<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20241211125130 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Create external service configuration table.';
    }

    public function up(Schema $schema): void
    {
        $this->addSql('CREATE TABLE "external_service_configuration" (service_name VARCHAR(255) NOT NULL, eligible_approvers JSON NOT NULL, PRIMARY KEY(service_name))');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('DROP TABLE "external_service_configuration"');
    }
}
