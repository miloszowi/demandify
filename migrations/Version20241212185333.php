<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20241212185333 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE "external_service_configuration" (external_service_name VARCHAR(255) NOT NULL, eligible_approvers JSON NOT NULL, PRIMARY KEY(external_service_name))');
        $this->addSql('CREATE TABLE "notifications" (demand_uuid UUID NOT NULL, channel VARCHAR(255) NOT NULL, type VARCHAR(255) NOT NULL, extra_data JSON NOT NULL, PRIMARY KEY(demand_uuid))');
        $this->addSql('COMMENT ON COLUMN "notifications".demand_uuid IS \'(DC2Type:uuid)\'');
        $this->addSql('ALTER TABLE "notifications" ADD CONSTRAINT FK_6000B0D3FBDF0E0 FOREIGN KEY (demand_uuid) REFERENCES "demand" (uuid) ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE "notifications" DROP CONSTRAINT FK_6000B0D3FBDF0E0');
        $this->addSql('DROP TABLE "external_service_configuration"');
        $this->addSql('DROP TABLE "notifications"');
    }
}
