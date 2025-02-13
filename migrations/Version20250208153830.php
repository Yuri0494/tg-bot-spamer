<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20250208153830 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        $this->addSql('ALTER TABLE "user" ADD prev_command VARCHAR(255) DEFAULT NULL');
        $this->addSql('ALTER TABLE "user" RENAME COLUMN last_command TO current_command');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('ALTER TABLE "user" DROP current_command');
        $this->addSql('ALTER TABLE "user" DROP prev_command');
    }
}
