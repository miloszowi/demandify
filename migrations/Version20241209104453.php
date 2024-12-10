<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20241209104453 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Creates demand, user and user_social_account tables.';
    }

    public function up(Schema $schema): void
    {
        $this->addSql('CREATE TABLE "demand" (uuid UUID NOT NULL, status VARCHAR(15) NOT NULL, requester_uuid UUID NOT NULL, approver_uuid UUID DEFAULT NULL, service VARCHAR(255) NOT NULL, content TEXT NOT NULL, reason TEXT NOT NULL, created_at TIMESTAMP(0) WITHOUT TIME ZONE NOT NULL, updated_at TIMESTAMP(0) WITHOUT TIME ZONE NOT NULL, PRIMARY KEY(uuid))');
        $this->addSql('CREATE INDEX service_idx ON "demand" (service)');
        $this->addSql('COMMENT ON COLUMN "demand".uuid IS \'(DC2Type:uuid)\'');
        $this->addSql('COMMENT ON COLUMN "demand".requester_uuid IS \'(DC2Type:uuid)\'');
        $this->addSql('COMMENT ON COLUMN "demand".approver_uuid IS \'(DC2Type:uuid)\'');
        $this->addSql('COMMENT ON COLUMN "demand".created_at IS \'(DC2Type:datetime_immutable)\'');
        $this->addSql('COMMENT ON COLUMN "demand".updated_at IS \'(DC2Type:datetime_immutable)\'');
        $this->addSql('CREATE TABLE "user" (uuid UUID NOT NULL, roles JSON NOT NULL, name VARCHAR(255) NOT NULL, email VARCHAR(255) NOT NULL, created_at TIMESTAMP(0) WITH TIME ZONE NOT NULL, updated_at TIMESTAMP(0) WITH TIME ZONE NOT NULL, PRIMARY KEY(uuid))');
        $this->addSql('CREATE UNIQUE INDEX UNIQ_8D93D649E7927C74 ON "user" (email)');
        $this->addSql('COMMENT ON COLUMN "user".uuid IS \'(DC2Type:uuid)\'');
        $this->addSql('COMMENT ON COLUMN "user".created_at IS \'(DC2Type:datetimetz_immutable)\'');
        $this->addSql('COMMENT ON COLUMN "user".updated_at IS \'(DC2Type:datetimetz_immutable)\'');
        $this->addSql('CREATE TABLE "user_social_account" (type VARCHAR(255) NOT NULL, user_uuid UUID NOT NULL, external_id VARCHAR(255) NOT NULL, extra_data JSON NOT NULL, PRIMARY KEY(user_uuid, type))');
        $this->addSql('CREATE INDEX IDX_99C85C60ABFE1C6F ON "user_social_account" (user_uuid)');
        $this->addSql('COMMENT ON COLUMN "user_social_account".user_uuid IS \'(DC2Type:uuid)\'');
        $this->addSql('ALTER TABLE "user_social_account" ADD CONSTRAINT FK_99C85C60ABFE1C6F FOREIGN KEY (user_uuid) REFERENCES "user" (uuid) ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('ALTER TABLE "user_social_account" DROP CONSTRAINT FK_99C85C60ABFE1C6F');
        $this->addSql('DROP TABLE "demand"');
        $this->addSql('DROP TABLE "user"');
        $this->addSql('DROP TABLE "user_social_account"');
    }
}
