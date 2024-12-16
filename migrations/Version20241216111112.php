<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20241216111112 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE external_service_configuration ALTER eligible_approvers TYPE JSON');
        $this->addSql('ALTER TABLE notifications ADD content TEXT NOT NULL');
        $this->addSql('ALTER TABLE notifications ADD attachments JSON NOT NULL');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE "notifications" DROP content');
        $this->addSql('ALTER TABLE "notifications" DROP attachments');
        $this->addSql('ALTER TABLE "external_service_configuration" ALTER eligible_approvers TYPE JSON');
    }
}
