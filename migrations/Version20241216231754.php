<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20241216231754 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE invitation (id SERIAL NOT NULL, guest_id INT NOT NULL, event_id INT NOT NULL, status VARCHAR(255) NOT NULL, PRIMARY KEY(id))');
        $this->addSql('CREATE INDEX IDX_F11D61A29A4AA658 ON invitation (guest_id)');
        $this->addSql('CREATE INDEX IDX_F11D61A271F7E88B ON invitation (event_id)');
        $this->addSql('ALTER TABLE invitation ADD CONSTRAINT FK_F11D61A29A4AA658 FOREIGN KEY (guest_id) REFERENCES profile (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE invitation ADD CONSTRAINT FK_F11D61A271F7E88B FOREIGN KEY (event_id) REFERENCES event (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE SCHEMA public');
        $this->addSql('ALTER TABLE invitation DROP CONSTRAINT FK_F11D61A29A4AA658');
        $this->addSql('ALTER TABLE invitation DROP CONSTRAINT FK_F11D61A271F7E88B');
        $this->addSql('DROP TABLE invitation');
    }
}
