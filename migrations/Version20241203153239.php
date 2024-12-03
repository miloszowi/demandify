<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20241203153239 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Creates table user & demand';
    }

    public function up(Schema $schema): void
    {
        $this->addSql('CREATE TABLE "demand" (uuid UUID NOT NULL, status VARCHAR(15) NOT NULL, approver_uuid UUID DEFAULT NULL, created_at TIMESTAMP(0) WITHOUT TIME ZONE NOT NULL, updated_at TIMESTAMP(0) WITHOUT TIME ZONE NOT NULL, requester_uuid UUID NOT NULL, service VARCHAR(255) NOT NULL, content TEXT NOT NULL, reason TEXT NOT NULL, PRIMARY KEY(uuid))');
        $this->addSql('CREATE INDEX service_idx ON "demand" (service)');
        $this->addSql('COMMENT ON COLUMN "demand".uuid IS \'(DC2Type:uuid)\'');
        $this->addSql('COMMENT ON COLUMN "demand".approver_uuid IS \'(DC2Type:uuid)\'');
        $this->addSql('COMMENT ON COLUMN "demand".created_at IS \'(DC2Type:datetime_immutable)\'');
        $this->addSql('COMMENT ON COLUMN "demand".updated_at IS \'(DC2Type:datetime_immutable)\'');
        $this->addSql('COMMENT ON COLUMN "demand".requester_uuid IS \'(DC2Type:uuid)\'');
        $this->addSql('CREATE TABLE "user" (uuid UUID NOT NULL, password VARCHAR(255) NOT NULL, roles JSON NOT NULL, created_at TIMESTAMP(0) WITH TIME ZONE NOT NULL, updated_at TIMESTAMP(0) WITH TIME ZONE NOT NULL, first_name VARCHAR(255) NOT NULL, last_name VARCHAR(255) NOT NULL, email VARCHAR(255) NOT NULL, PRIMARY KEY(uuid))');
        $this->addSql('CREATE UNIQUE INDEX UNIQ_8D93D649E7927C74 ON "user" (email)');
        $this->addSql('COMMENT ON COLUMN "user".uuid IS \'(DC2Type:uuid)\'');
        $this->addSql('COMMENT ON COLUMN "user".created_at IS \'(DC2Type:datetimetz_immutable)\'');
        $this->addSql('COMMENT ON COLUMN "user".updated_at IS \'(DC2Type:datetimetz_immutable)\'');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('DROP TABLE "demand"');
        $this->addSql('DROP TABLE "user"');
    }
}
