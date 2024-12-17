<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20241217093834 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE suggestion (id SERIAL NOT NULL, profile_id INT DEFAULT NULL, event_contributions_id INT DEFAULT NULL, title VARCHAR(255) NOT NULL, description VARCHAR(255) NOT NULL, supported BOOLEAN NOT NULL, PRIMARY KEY(id))');
        $this->addSql('CREATE INDEX IDX_DD80F31BCCFA12B8 ON suggestion (profile_id)');
        $this->addSql('CREATE INDEX IDX_DD80F31B744FCF83 ON suggestion (event_contributions_id)');
        $this->addSql('ALTER TABLE suggestion ADD CONSTRAINT FK_DD80F31BCCFA12B8 FOREIGN KEY (profile_id) REFERENCES profile (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE suggestion ADD CONSTRAINT FK_DD80F31B744FCF83 FOREIGN KEY (event_contributions_id) REFERENCES contributions (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE SCHEMA public');
        $this->addSql('ALTER TABLE suggestion DROP CONSTRAINT FK_DD80F31BCCFA12B8');
        $this->addSql('ALTER TABLE suggestion DROP CONSTRAINT FK_DD80F31B744FCF83');
        $this->addSql('DROP TABLE suggestion');
    }
}
