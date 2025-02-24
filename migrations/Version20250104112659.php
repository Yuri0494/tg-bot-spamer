<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20250104112659 extends AbstractMigration
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
        $this->addSql('ALTER TABLE "user" DROP last_name');
        $this->addSql('ALTER TABLE "user" DROP user_name');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE SCHEMA public');
        $this->addSql('ALTER TABLE girl_images ALTER link TYPE TEXT');
        $this->addSql('ALTER TABLE girl_images ALTER link TYPE TEXT');
        $this->addSql('ALTER TABLE "user" ADD last_name VARCHAR(255) NOT NULL');
        $this->addSql('ALTER TABLE "user" ADD user_name VARCHAR(255) NOT NULL');
        $this->addSql('ALTER TABLE girl ALTER personal_info TYPE TEXT');
        $this->addSql('ALTER TABLE girl ALTER personal_info TYPE TEXT');
    }
}
