<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Migration for Photo table
 */
final class Version20251110140000 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Create photo table for gallery feature';
    }

    public function up(Schema $schema): void
    {
        // Create photo table
        $this->addSql('CREATE TABLE photo (
            id SERIAL PRIMARY KEY,
            url TEXT NOT NULL,
            caption VARCHAR(255) DEFAULT NULL,
            original_name VARCHAR(255) DEFAULT NULL,
            user_id INT NOT NULL,
            uploaded_at TIMESTAMP(0) WITHOUT TIME ZONE NOT NULL,
            CONSTRAINT FK_14B78418A76ED395 FOREIGN KEY (user_id) REFERENCES "user" (id) NOT DEFERRABLE INITIALLY IMMEDIATE
        )');
        $this->addSql('CREATE INDEX IDX_14B78418A76ED395 ON photo (user_id)');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('DROP TABLE photo');
    }
}
