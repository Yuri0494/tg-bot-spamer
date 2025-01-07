<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20250104153654 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE SEQUENCE subscriber_subscription_id_seq INCREMENT BY 1 MINVALUE 1 START 1');
        $this->addSql('CREATE TABLE subscriber_subscription (id INT NOT NULL, subscriber_id INT NOT NULL, subscription_id INT NOT NULL, last_watched_series INT DEFAULT 0, PRIMARY KEY(id))');
        $this->addSql('ALTER TABLE girl ALTER personal_info TYPE TEXT');
        $this->addSql('ALTER TABLE girl_images ALTER link TYPE TEXT');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE SCHEMA public');
        $this->addSql('DROP SEQUENCE subscriber_subscription_id_seq CASCADE');
        $this->addSql('DROP TABLE subscriber_subscription');
        $this->addSql('ALTER TABLE girl_images ALTER link TYPE TEXT');
        $this->addSql('ALTER TABLE girl_images ALTER link TYPE TEXT');
        $this->addSql('ALTER TABLE girl ALTER personal_info TYPE TEXT');
        $this->addSql('ALTER TABLE girl ALTER personal_info TYPE TEXT');
    }
}
