<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20241216135010 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE event_profile (event_id INT NOT NULL, profile_id INT NOT NULL, PRIMARY KEY(event_id, profile_id))');
        $this->addSql('CREATE INDEX IDX_40003A071F7E88B ON event_profile (event_id)');
        $this->addSql('CREATE INDEX IDX_40003A0CCFA12B8 ON event_profile (profile_id)');
        $this->addSql('ALTER TABLE event_profile ADD CONSTRAINT FK_40003A071F7E88B FOREIGN KEY (event_id) REFERENCES event (id) ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE event_profile ADD CONSTRAINT FK_40003A0CCFA12B8 FOREIGN KEY (profile_id) REFERENCES profile (id) ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE SCHEMA public');
        $this->addSql('ALTER TABLE event_profile DROP CONSTRAINT FK_40003A071F7E88B');
        $this->addSql('ALTER TABLE event_profile DROP CONSTRAINT FK_40003A0CCFA12B8');
        $this->addSql('DROP TABLE event_profile');
    }
}
