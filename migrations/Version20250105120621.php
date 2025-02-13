<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20250105120621 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        $this->addSql('ALTER TABLE subscriber ALTER subscriber_id TYPE BIGINT USING subscriber_id::bigint');
        $this->addSql('ALTER TABLE subscriber_subscription ALTER subscriber_id TYPE BIGINT USING subscriber_id::bigint');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('ALTER TABLE subscriber_subscription ALTER subscriber_id TYPE INT');
        $this->addSql('ALTER TABLE subscriber ALTER subscriber_id TYPE VARCHAR(255)');
    }
}
