<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20241122231456 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE SEQUENCE girl_id_seq INCREMENT BY 1 MINVALUE 1 START 1');
        $this->addSql('CREATE SEQUENCE girl_images_id_seq INCREMENT BY 1 MINVALUE 1 START 1');
        $this->addSql('CREATE SEQUENCE sketches_id_seq INCREMENT BY 1 MINVALUE 1 START 1');
        $this->addSql('CREATE SEQUENCE users_id_seq INCREMENT BY 1 MINVALUE 1 START 1');
        $this->addSql('CREATE TABLE girl (id INT NOT NULL, personal_info TEXT DEFAULT NULL, PRIMARY KEY(id))');
        $this->addSql('CREATE TABLE girl_images (id INT NOT NULL, girl_id INT NOT NULL, link TEXT NOT NULL, PRIMARY KEY(id))');
        $this->addSql('CREATE INDEX IDX_E2CBC5DD976414B1 ON girl_images (girl_id)');
        $this->addSql('CREATE TABLE sketches (id INT NOT NULL, sketch_name VARCHAR(255) NOT NULL, series_number INT NOT NULL, is_watched BOOLEAN NOT NULL, link VARCHAR(255) NOT NULL, PRIMARY KEY(id))');
        $this->addSql('CREATE TABLE users (id INT NOT NULL, chat_id INT NOT NULL, first_name VARCHAR(255) NOT NULL, username VARCHAR(255) NOT NULL, type VARCHAR(255) NOT NULL, language_code VARCHAR(255) DEFAULT NULL, PRIMARY KEY(id))');
        $this->addSql('ALTER TABLE girl_images ADD CONSTRAINT FK_E2CBC5DD976414B1 FOREIGN KEY (girl_id) REFERENCES girl (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE SCHEMA public');
        $this->addSql('DROP SEQUENCE girl_id_seq CASCADE');
        $this->addSql('DROP SEQUENCE girl_images_id_seq CASCADE');
        $this->addSql('DROP SEQUENCE sketches_id_seq CASCADE');
        $this->addSql('DROP SEQUENCE users_id_seq CASCADE');
        $this->addSql('ALTER TABLE girl_images DROP CONSTRAINT FK_E2CBC5DD976414B1');
        $this->addSql('DROP TABLE girl');
        $this->addSql('DROP TABLE girl_images');
        $this->addSql('DROP TABLE sketches');
        $this->addSql('DROP TABLE users');
    }
}
