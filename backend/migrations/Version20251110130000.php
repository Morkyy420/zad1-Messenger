<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Migration for adding media attachments to messages and Event/Todo tables
 */
final class Version20251110130000 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Add attachment fields to message table and create event and todo tables';
    }

    public function up(Schema $schema): void
    {
        // Add attachment fields to message table
        $this->addSql('ALTER TABLE message ADD attachment_type VARCHAR(50) DEFAULT NULL');
        $this->addSql('ALTER TABLE message ADD attachment_url TEXT DEFAULT NULL');
        $this->addSql('ALTER TABLE message ADD attachment_name VARCHAR(255) DEFAULT NULL');

        // Create event table
        $this->addSql('CREATE TABLE event (
            id SERIAL PRIMARY KEY,
            title VARCHAR(255) NOT NULL,
            description TEXT DEFAULT NULL,
            date DATE NOT NULL,
            time VARCHAR(10) NOT NULL,
            author_id INT NOT NULL,
            created_at TIMESTAMP(0) WITHOUT TIME ZONE NOT NULL,
            CONSTRAINT FK_3BAE0AA7F675F31B FOREIGN KEY (author_id) REFERENCES "user" (id) NOT DEFERRABLE INITIALLY IMMEDIATE
        )');
        $this->addSql('CREATE INDEX IDX_3BAE0AA7F675F31B ON event (author_id)');

        // Create todo table
        $this->addSql('CREATE TABLE todo (
            id SERIAL PRIMARY KEY,
            text VARCHAR(500) NOT NULL,
            completed BOOLEAN NOT NULL DEFAULT FALSE,
            user_id INT NOT NULL,
            created_at TIMESTAMP(0) WITHOUT TIME ZONE NOT NULL,
            CONSTRAINT FK_5A0EB6A0A76ED395 FOREIGN KEY (user_id) REFERENCES "user" (id) NOT DEFERRABLE INITIALLY IMMEDIATE
        )');
        $this->addSql('CREATE INDEX IDX_5A0EB6A0A76ED395 ON todo (user_id)');
    }

    public function down(Schema $schema): void
    {
        // Remove attachment fields from message table
        $this->addSql('ALTER TABLE message DROP attachment_type');
        $this->addSql('ALTER TABLE message DROP attachment_url');
        $this->addSql('ALTER TABLE message DROP attachment_name');

        // Drop todo table
        $this->addSql('DROP TABLE todo');

        // Drop event table
        $this->addSql('DROP TABLE event');
    }
}
