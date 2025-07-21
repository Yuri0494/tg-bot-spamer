<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20250721123834 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE SEQUENCE chat_id_seq INCREMENT BY 1 MINVALUE 1 START 1');
        $this->addSql('CREATE SEQUENCE girl_images_id_seq INCREMENT BY 1 MINVALUE 1 START 1');
        $this->addSql('CREATE SEQUENCE sketches_id_seq INCREMENT BY 1 MINVALUE 1 START 1');
        $this->addSql('CREATE SEQUENCE subscriber_id_seq INCREMENT BY 1 MINVALUE 1 START 1');
        $this->addSql('CREATE SEQUENCE subscriber_subscription_id_seq INCREMENT BY 1 MINVALUE 1 START 1');
        $this->addSql('CREATE SEQUENCE subscription_id_seq INCREMENT BY 1 MINVALUE 1 START 1');
        $this->addSql('CREATE SEQUENCE "user_id_seq" INCREMENT BY 1 MINVALUE 1 START 1');
        $this->addSql('CREATE TABLE chat (id INT NOT NULL, title VARCHAR(255) DEFAULT NULL, type VARCHAR(255) NOT NULL, chat_id BIGINT NOT NULL, PRIMARY KEY(id))');
        $this->addSql('CREATE TABLE girl (id SERIAL NOT NULL, personal_info TEXT DEFAULT NULL, is_watched BOOLEAN DEFAULT false NOT NULL, PRIMARY KEY(id))');
        $this->addSql('CREATE TABLE girl_images (id INT NOT NULL, girl_id INT NOT NULL, link TEXT NOT NULL, PRIMARY KEY(id))');
        $this->addSql('CREATE INDEX IDX_E2CBC5DD976414B1 ON girl_images (girl_id)');
        $this->addSql('CREATE TABLE sketches (id INT NOT NULL, sketch_name VARCHAR(255) NOT NULL, series_number INT NOT NULL, is_watched BOOLEAN NOT NULL, link VARCHAR(255) NOT NULL, season INT NOT NULL, PRIMARY KEY(id))');
        $this->addSql('CREATE TABLE subscriber (id INT NOT NULL, subscriber_id BIGINT NOT NULL, PRIMARY KEY(id))');
        $this->addSql('CREATE UNIQUE INDEX UNIQ_AD005B697808B1AD ON subscriber (subscriber_id)');
        $this->addSql('CREATE TABLE subscriber_subscription (id INT NOT NULL, subscriber_id BIGINT NOT NULL, subscription_id INT NOT NULL, last_watched_series INT NOT NULL, parameters JSON NOT NULL, PRIMARY KEY(id))');
        $this->addSql('CREATE TABLE subscription (id INT NOT NULL, name VARCHAR(255) NOT NULL, category VARCHAR(255) NOT NULL, code VARCHAR(255) NOT NULL, PRIMARY KEY(id))');
        $this->addSql('CREATE UNIQUE INDEX UNIQ_A3C664D377153098 ON subscription (code)');
        $this->addSql('CREATE TABLE "user" (id INT NOT NULL, tg_id INT NOT NULL, first_name VARCHAR(255) NOT NULL, last_name VARCHAR(255) DEFAULT NULL, user_name VARCHAR(255) DEFAULT NULL, current_command VARCHAR(255) DEFAULT NULL, prev_command VARCHAR(255) DEFAULT NULL, PRIMARY KEY(id))');
        $this->addSql('CREATE UNIQUE INDEX UNIQ_8D93D649F581628A ON "user" (tg_id)');
        $this->addSql('ALTER TABLE girl_images ADD CONSTRAINT FK_E2CBC5DD976414B1 FOREIGN KEY (girl_id) REFERENCES girl (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE SCHEMA public');
        $this->addSql('DROP SEQUENCE chat_id_seq CASCADE');
        $this->addSql('DROP SEQUENCE girl_images_id_seq CASCADE');
        $this->addSql('DROP SEQUENCE sketches_id_seq CASCADE');
        $this->addSql('DROP SEQUENCE subscriber_id_seq CASCADE');
        $this->addSql('DROP SEQUENCE subscriber_subscription_id_seq CASCADE');
        $this->addSql('DROP SEQUENCE subscription_id_seq CASCADE');
        $this->addSql('DROP SEQUENCE "user_id_seq" CASCADE');
        $this->addSql('ALTER TABLE girl_images DROP CONSTRAINT FK_E2CBC5DD976414B1');
        $this->addSql('DROP TABLE chat');
        $this->addSql('DROP TABLE girl');
        $this->addSql('DROP TABLE girl_images');
        $this->addSql('DROP TABLE sketches');
        $this->addSql('DROP TABLE subscriber');
        $this->addSql('DROP TABLE subscriber_subscription');
        $this->addSql('DROP TABLE subscription');
        $this->addSql('DROP TABLE "user"');
    }
}
