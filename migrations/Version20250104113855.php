<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20250104113855 extends AbstractMigration
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
        $this->addSql('CREATE UNIQUE INDEX UNIQ_8D93D649F581628A ON "user" (tg_id)');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE SCHEMA public');
        $this->addSql('ALTER TABLE girl_images ALTER link TYPE TEXT');
        $this->addSql('ALTER TABLE girl_images ALTER link TYPE TEXT');
        $this->addSql('DROP INDEX UNIQ_8D93D649F581628A');
        $this->addSql('ALTER TABLE girl ALTER personal_info TYPE TEXT');
        $this->addSql('ALTER TABLE girl ALTER personal_info TYPE TEXT');
    }
}
