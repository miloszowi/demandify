<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20241211123213 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Create task table.';
    }

    public function up(Schema $schema): void
    {
        $this->addSql('CREATE TABLE task (uuid UUID NOT NULL, demand_uuid UUID DEFAULT NULL, executed_at TIMESTAMP(0) WITH TIME ZONE NOT NULL, success BOOLEAN NOT NULL, execution_time INT NOT NULL, result_path VARCHAR(255) DEFAULT NULL, PRIMARY KEY(uuid))');
        $this->addSql('CREATE UNIQUE INDEX UNIQ_527EDB25FBDF0E0 ON task (demand_uuid)');
        $this->addSql('COMMENT ON COLUMN task.uuid IS \'(DC2Type:uuid)\'');
        $this->addSql('COMMENT ON COLUMN task.demand_uuid IS \'(DC2Type:uuid)\'');
        $this->addSql('COMMENT ON COLUMN task.executed_at IS \'(DC2Type:datetimetz_immutable)\'');
        $this->addSql('ALTER TABLE task ADD CONSTRAINT FK_527EDB25FBDF0E0 FOREIGN KEY (demand_uuid) REFERENCES "demand" (uuid) NOT DEFERRABLE INITIALLY IMMEDIATE');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('ALTER TABLE task DROP CONSTRAINT FK_527EDB25FBDF0E0');
        $this->addSql('DROP TABLE task');
    }
}
