<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20250208120646 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        $this->addSql('ALTER TABLE subscriber_subscription ADD parameters JSON');
        $this->addSql('UPDATE subscriber_subscription SET parameters = \'{}\' WHERE parameters IS NULL');
        $this->addSql('ALTER TABLE subscriber_subscription ALTER COLUMN parameters SET DEFAULT \'{}\'');
        $this->addSql('ALTER TABLE subscriber_subscription ALTER parameters SET NOT NULL');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('ALTER TABLE subscriber_subscription DROP parameters');
    }
}
