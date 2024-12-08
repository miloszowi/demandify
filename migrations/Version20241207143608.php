<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20241207143608 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Create Foreign key to user table.';
    }

    public function up(Schema $schema): void
    {
        $this->addSql('ALTER TABLE user_social_account ADD CONSTRAINT FK_99C85C60ABFE1C6F FOREIGN KEY (user_uuid) REFERENCES "user" (uuid) ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('CREATE INDEX IDX_99C85C60ABFE1C6F ON user_social_account (user_uuid)');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('ALTER TABLE "user_social_account" DROP CONSTRAINT FK_99C85C60ABFE1C6F');
        $this->addSql('DROP INDEX IDX_99C85C60ABFE1C6F');
    }
}
