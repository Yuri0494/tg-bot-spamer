<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20250104223451 extends AbstractMigration
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
        $this->addSql('ALTER TABLE subscriber_subscription ALTER last_watched_series DROP DEFAULT');
        $this->addSql('ALTER TABLE subscriber_subscription ALTER last_watched_series SET NOT NULL');
        $this->addSql('ALTER TABLE subscription ADD code VARCHAR(255) NOT NULL');
        $this->addSql('CREATE UNIQUE INDEX UNIQ_A3C664D377153098 ON subscription (code)');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE SCHEMA public');
        $this->addSql('ALTER TABLE girl ALTER personal_info TYPE TEXT');
        $this->addSql('ALTER TABLE girl ALTER personal_info TYPE TEXT');
        $this->addSql('ALTER TABLE subscriber_subscription ALTER last_watched_series SET DEFAULT 0');
        $this->addSql('ALTER TABLE subscriber_subscription ALTER last_watched_series DROP NOT NULL');
        $this->addSql('DROP INDEX UNIQ_A3C664D377153098');
        $this->addSql('ALTER TABLE subscription DROP code');
        $this->addSql('ALTER TABLE girl_images ALTER link TYPE TEXT');
        $this->addSql('ALTER TABLE girl_images ALTER link TYPE TEXT');
    }
}
