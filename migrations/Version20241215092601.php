<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20241215092601 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE "demand" (uuid UUID NOT NULL, approver_uuid UUID DEFAULT NULL, requester_uuid UUID NOT NULL, status VARCHAR(15) NOT NULL, created_at TIMESTAMP(0) WITHOUT TIME ZONE NOT NULL, updated_at TIMESTAMP(0) WITHOUT TIME ZONE NOT NULL, service VARCHAR(255) NOT NULL, content TEXT NOT NULL, reason TEXT NOT NULL, PRIMARY KEY(uuid))');
        $this->addSql('CREATE INDEX IDX_428D7973C83C0DB1 ON "demand" (approver_uuid)');
        $this->addSql('CREATE INDEX IDX_428D7973FA1AE32E ON "demand" (requester_uuid)');
        $this->addSql('CREATE INDEX service_idx ON "demand" (service)');
        $this->addSql('COMMENT ON COLUMN "demand".uuid IS \'(DC2Type:uuid)\'');
        $this->addSql('COMMENT ON COLUMN "demand".approver_uuid IS \'(DC2Type:uuid)\'');
        $this->addSql('COMMENT ON COLUMN "demand".requester_uuid IS \'(DC2Type:uuid)\'');
        $this->addSql('COMMENT ON COLUMN "demand".created_at IS \'(DC2Type:datetime_immutable)\'');
        $this->addSql('COMMENT ON COLUMN "demand".updated_at IS \'(DC2Type:datetime_immutable)\'');
        $this->addSql('CREATE TABLE "external_service_configuration" (external_service_name VARCHAR(255) NOT NULL, eligible_approvers JSON NOT NULL, PRIMARY KEY(external_service_name))');
        $this->addSql('CREATE TABLE "notifications" (demand_uuid UUID NOT NULL, created_at TIMESTAMP(0) WITH TIME ZONE NOT NULL, type VARCHAR(255) NOT NULL, notification_identifier VARCHAR(255) NOT NULL, channel VARCHAR(255) NOT NULL, social_account_type VARCHAR(255) NOT NULL, PRIMARY KEY(demand_uuid))');
        $this->addSql('COMMENT ON COLUMN "notifications".demand_uuid IS \'(DC2Type:uuid)\'');
        $this->addSql('COMMENT ON COLUMN "notifications".created_at IS \'(DC2Type:datetimetz_immutable)\'');
        $this->addSql('CREATE TABLE task (uuid UUID NOT NULL, demand_uuid UUID DEFAULT NULL, executed_at TIMESTAMP(0) WITH TIME ZONE NOT NULL, success BOOLEAN NOT NULL, execution_time INT NOT NULL, result_path VARCHAR(255) DEFAULT NULL, PRIMARY KEY(uuid))');
        $this->addSql('CREATE UNIQUE INDEX UNIQ_527EDB25FBDF0E0 ON task (demand_uuid)');
        $this->addSql('COMMENT ON COLUMN task.uuid IS \'(DC2Type:uuid)\'');
        $this->addSql('COMMENT ON COLUMN task.demand_uuid IS \'(DC2Type:uuid)\'');
        $this->addSql('COMMENT ON COLUMN task.executed_at IS \'(DC2Type:datetimetz_immutable)\'');
        $this->addSql('CREATE TABLE "user" (uuid UUID NOT NULL, created_at TIMESTAMP(0) WITH TIME ZONE NOT NULL, updated_at TIMESTAMP(0) WITH TIME ZONE NOT NULL, roles JSON NOT NULL, name VARCHAR(255) NOT NULL, email VARCHAR(255) NOT NULL, PRIMARY KEY(uuid))');
        $this->addSql('CREATE UNIQUE INDEX UNIQ_8D93D649E7927C74 ON "user" (email)');
        $this->addSql('COMMENT ON COLUMN "user".uuid IS \'(DC2Type:uuid)\'');
        $this->addSql('COMMENT ON COLUMN "user".created_at IS \'(DC2Type:datetimetz_immutable)\'');
        $this->addSql('COMMENT ON COLUMN "user".updated_at IS \'(DC2Type:datetimetz_immutable)\'');
        $this->addSql('CREATE TABLE "user_social_account" (type VARCHAR(255) NOT NULL, user_uuid UUID NOT NULL, external_id VARCHAR(255) NOT NULL, extra_data JSON NOT NULL, PRIMARY KEY(user_uuid, type))');
        $this->addSql('CREATE INDEX IDX_99C85C60ABFE1C6F ON "user_social_account" (user_uuid)');
        $this->addSql('COMMENT ON COLUMN "user_social_account".user_uuid IS \'(DC2Type:uuid)\'');
        $this->addSql('ALTER TABLE "demand" ADD CONSTRAINT FK_428D7973C83C0DB1 FOREIGN KEY (approver_uuid) REFERENCES "user" (uuid) NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE "demand" ADD CONSTRAINT FK_428D7973FA1AE32E FOREIGN KEY (requester_uuid) REFERENCES "user" (uuid) NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE task ADD CONSTRAINT FK_527EDB25FBDF0E0 FOREIGN KEY (demand_uuid) REFERENCES "demand" (uuid) NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE "user_social_account" ADD CONSTRAINT FK_99C85C60ABFE1C6F FOREIGN KEY (user_uuid) REFERENCES "user" (uuid) ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE "demand" DROP CONSTRAINT FK_428D7973C83C0DB1');
        $this->addSql('ALTER TABLE "demand" DROP CONSTRAINT FK_428D7973FA1AE32E');
        $this->addSql('ALTER TABLE task DROP CONSTRAINT FK_527EDB25FBDF0E0');
        $this->addSql('ALTER TABLE "user_social_account" DROP CONSTRAINT FK_99C85C60ABFE1C6F');
        $this->addSql('DROP TABLE "demand"');
        $this->addSql('DROP TABLE "external_service_configuration"');
        $this->addSql('DROP TABLE "notifications"');
        $this->addSql('DROP TABLE task');
        $this->addSql('DROP TABLE "user"');
        $this->addSql('DROP TABLE "user_social_account"');
    }
}
