<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20241216094848 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE profile (id SERIAL NOT NULL, user_profile_id INT NOT NULL, display_name VARCHAR(20) NOT NULL, PRIMARY KEY(id))');
        $this->addSql('CREATE UNIQUE INDEX UNIQ_8157AA0F6B9DD454 ON profile (user_profile_id)');
        $this->addSql('ALTER TABLE profile ADD CONSTRAINT FK_8157AA0F6B9DD454 FOREIGN KEY (user_profile_id) REFERENCES "user" (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE event ADD profile_id INT DEFAULT NULL');
        $this->addSql('ALTER TABLE event ADD CONSTRAINT FK_3BAE0AA7CCFA12B8 FOREIGN KEY (profile_id) REFERENCES profile (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('CREATE INDEX IDX_3BAE0AA7CCFA12B8 ON event (profile_id)');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE SCHEMA public');
        $this->addSql('ALTER TABLE event DROP CONSTRAINT FK_3BAE0AA7CCFA12B8');
        $this->addSql('ALTER TABLE profile DROP CONSTRAINT FK_8157AA0F6B9DD454');
        $this->addSql('DROP TABLE profile');
        $this->addSql('DROP INDEX IDX_3BAE0AA7CCFA12B8');
        $this->addSql('ALTER TABLE event DROP profile_id');
    }
}
