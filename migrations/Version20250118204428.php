<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20250118204428 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE girl (id SERIAL NOT NULL, personal_info TEXT DEFAULT NULL, is_watched BOOLEAN DEFAULT false NOT NULL, PRIMARY KEY(id))');
        $this->addSql('CREATE TABLE girl_images (id INT NOT NULL, girl_id INT NOT NULL, link TEXT NOT NULL, PRIMARY KEY(id))');
        $this->addSql('CREATE INDEX IDX_E2CBC5DD976414B1 ON girl_images (girl_id)');
        $this->addSql('ALTER TABLE girl_images ADD CONSTRAINT FK_E2CBC5DD976414B1 FOREIGN KEY (girl_id) REFERENCES girl (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('ALTER TABLE girl_images DROP CONSTRAINT FK_E2CBC5DD976414B1');
        $this->addSql('DROP TABLE girl');
        $this->addSql('DROP TABLE girl_images');
    }
}
