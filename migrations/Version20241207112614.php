<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20241207112614 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Creates user social account table.';
    }

    public function up(Schema $schema): void
    {
        $this->addSql('CREATE TABLE "user_social_account" (user_uuid UUID NOT NULL, type VARCHAR(255) NOT NULL, external_id VARCHAR(255) NOT NULL, extra_data JSON NOT NULL, PRIMARY KEY(user_uuid, type))');
        $this->addSql('COMMENT ON COLUMN "user_social_account".user_uuid IS \'(DC2Type:uuid)\'');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('DROP TABLE "user_social_account"');
    }
}
