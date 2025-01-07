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
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE girl ALTER personal_info TYPE TEXT');
        $this->addSql('ALTER TABLE girl_images ALTER link TYPE TEXT');
        $this->addSql('ALTER TABLE subscriber ALTER subscriber_id TYPE BIGINT USING subscriber_id::bigint');
        $this->addSql('ALTER TABLE subscriber_subscription ALTER subscriber_id TYPE BIGINT USING subscriber_id::bigint');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE SCHEMA public');
        $this->addSql('ALTER TABLE subscriber_subscription ALTER subscriber_id TYPE INT');
        $this->addSql('ALTER TABLE girl ALTER personal_info TYPE TEXT');
        $this->addSql('ALTER TABLE girl ALTER personal_info TYPE TEXT');
        $this->addSql('ALTER TABLE subscriber ALTER subscriber_id TYPE VARCHAR(255)');
        $this->addSql('ALTER TABLE girl_images ALTER link TYPE TEXT');
        $this->addSql('ALTER TABLE girl_images ALTER link TYPE TEXT');
    }
}
